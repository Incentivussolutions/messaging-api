<?php

namespace App\Helpers;

use DB;
use Exception;
use App\Mail\Mailer;
use App\Mail\OtpMail;
use App\Models\Client;
use App\Models\MailLog;
use App\Models\EmailConfig;
use Illuminate\Support\Str;
use App\Models\StudentRegistration;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use Illuminate\Contracts\Encryption\DecryptException;

class Common {

    public static function encrypt($plain) {
        try {
            if (is_array($plain) || is_object($plain)) {
                $plain = json_encode($plain);
            }
            $encrypted = Crypt::encryptString($plain);
            return $encrypted;
        } catch (Exception $e) {
            Log::info($e);
            return null;
        }
    }

    public static function decrypt($encrypted) {
        try {
            $plain = Crypt::decryptString($encrypted);
            return $plain;
        } catch (DecryptException $e) {
            Log::info($e);
            return null;
        } catch (Exception $e) {
            Log::info($e);
            return null;
        }
    }

    public static function hashMake($value) {
        return Hash::make($value);
    }

    public static function hashCheck($value, $hash_value) {
        return Hash::check($value, $hash_value);
    }

    public static function validator($request, $fields = []) {
        try {
            if (is_array($fields) && $request) {
                // Validation given Data's
                $validator = Validator::make($request->all(), $fields);
                
                return $validator;
            }
        } catch(\Exception $e) {
            Log::info($e);
            return null;
        }
    }

    public static function searchIndex($query, $request, $columns) {
        try {
            $request = collect($request);
            $columns = collect($columns);
            if ($query && $request && isset($request['search']) && isset($request['search']['field']) && isset($request['search']['text']) && count($columns)) {
                $column = @$columns[$request['search']['field']];
    
                if ($column && $request['search']['text']) {
                    $query = $query->where($column, 'LIKE', '%'. $request['search']['text'] .'%');
                }
            }
    
            return $query;
        } catch(Exception $e) {
            Log::info($e);
            return null;
        }
    }

    public static function orderIndex($request, $query) {
        if ($query && $request && isset($request->sort) && isset($request->sort['field']) && isset($request->sort['order'])) {
            $query  = $query->orderBy($request->sort['field'], $request->sort['order']);
        }

        return $query;
    }

    /**
    * Change the application DB
    */
    public static function changeClient($client_id) {
        $response = false;

        try {
            if ($client_id) {
                Config::set('database.default', 'client');
                $db_name = Config::get('database.connections.client.db_prefix').$client_id;
                Config::set('database.connections.client.database', $db_name);
                // If you want to use query builder without having to specify the connection
                $conn = DB::reconnect('client');
            } else if ($is_external == false) {
                Config::set('database.default', 'mysql');
                Config::set('database.connections.mysql.database', Config::get('database.connections.mysql.database'));
                // If you want to use query builder without having to specify the connection
                $conn = DB::reconnect('mysql');
            }

            if ($conn) {
                $response = true;
            }
            return $response;
        } catch(Exception $e) {
            Log::info($e);
            return $response;
        }
    }

    public static function sendMail($options = []) {
        $response = null;
        try {
            Config::set('mail.mailers.smtp.password', self::decrypt(config('mail.mailers.smtp.epassword')));
            $mail_config    = EmailConfig::where('is_default', '=', 1)->first();
            if ($mail_config) {
                $options['mail_config']   = $mail_config;
            }
            $cc_address     = (@$options['cc_address'] && is_array($options['cc_address'])) ? $options['cc_address'] : array();
            $bcc_address    = (@$options['bcc_address'] && is_array($options['bcc_address'])) ? $options['bcc_address'] : array();
            $attachments    = (@$options['attachments'] && is_array($options['attachments'])) ? $options['attachments'] : array();
            $path = null;
            if ($attachments) {
                $path = 'temp/attachments/'.date('YmdHis').'/';
            }
            $mail_attachments = array();
            foreach($attachments as $key => $attachment) {
                $file_name = $attachment->getClientOriginalName();
                AppStorage::storeFile($attachment, $path, $file_name);
                $mail_attachments[] = storage_path('app').'/'.$path.$file_name;
            }
            $options['attachments'] = $mail_attachments;
            $mail = Mail::to($options['email'])
                 ->cc($cc_address)
                 ->bcc($bcc_address);
            if (@$options['is_queue'] && $options['is_queue'] == true) {
                $mail->queue(new Mailer($options));
            } else {
                $mail->send(new Mailer($options));
            }
            $mail_content = (new Mailer($options))->render();
            $mail_log = new MailLog;
            $mail_log->school_id = @$options['school_id'];
            $mail_log->subject = @$options['subject'];
            $mail_log->mail_config_id = @$mail_config['id'];
            $mail_log->to_mail = @$options['email'];
            $mail_log->subject = @$options['subject'];
            $mail_log->created_user_id = @$options['user_id'];
            $mail_log->content = $mail_content;
            if (count(Mail::failures()) > 0) {
                $mail_log->status = 3;
                Log::info("Error sending mail");
                Log::info(Mail::failures());
            } else {
                $mail_log->status = 2;
                $response = true;
            }
            $mail_log->save();
            if (@$mail_attachments && @$mail_log) {
                // move mail attachments
                // Log::info($mail_log);
                $dest_path = AppStorage::$mail_attachment_store_path.$mail_log->id.'/';
                AppStorage::moveFiles($path, $dest_path);
                AppStorage::deleteFolder($path);
            }
            return $response;
        } catch(Exception $e) {
            Log::info($e);
            if (@$mail_log) {
                $mail_log->status = 3;
                $mail_log->save();
                if (@$mail_attachments) {
                    // move mail attachments
                    $dest_path = AppStorage::$mail_attachment_store_path.$mail_log->id.'/';
                    AppStorage::moveFiles($path, $dest_path);
                    AppStorage::deleteFolder($path);
                }
            }
            return $e;
        }
    }

    
    public static function getUUID() {
        $uniqid = null;
        try {
            $uniqid = Str::uuid();
            return $uniqid;
        } catch(Exception $e) {
            Log::info($e);
            return null;
        }
    }

}
?>