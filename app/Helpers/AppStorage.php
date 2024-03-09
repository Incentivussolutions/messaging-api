<?php

namespace App\Helpers;

// --default
use Illuminate\Support\Arr;
// File Save
use Illuminate\Support\Facades\Log;
// Image Optimizer
use Illuminate\Support\Facades\File;
// Laravel Array
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;



class AppStorage
{

    public static $client_store_path = 'clients/';
    public static $key_store_path = 'keys/';
    public static $template_store_path = 'templates/';
    public static $template_review_store_path = 'preview/';
    public static $header_store_path = 'headers/';
    public static $footer_store_path = 'footers/';
    public static $custom_store_path = 'custom/';
    /**
    * Application Global File upload
    * File (store, generate URL, delete)
    */
    public static function storeFile($file = null, $path = '', $file_name = '', $perfix = '', $is_encoded = false) {
        $local        = 'local';
        $pixels       = 100;
        $filesystems  = config('filesystems.default');
        $storage_path = config('filesystems.disks.'. $local .'.root').'/';
        // $file_name    = null;
        $response     = [];

        if (!empty($file) && !empty($path)) {
            // Generate file name
            $name       = $file->getClientOriginalName();
            $extension  = $file->getClientOriginalExtension();
            if (!$extension) return null;
            $file_name  = (!$file_name) ? $perfix.time().'.'.$extension : $file_name;
            if (Storage::disk($filesystems)->exists($file)) {
                self::deleteFile($file_name, $path);
            }
            if ($is_encoded) {
                $encoded = explode(',',file_get_contents($file))[1];
                Log::info($encoded);
                $contents = base64_decode($encoded);
                $upload = Storage::disk($filesystems)->put($path.$file_name, $contents);
            } else {
                $upload = Storage::disk($filesystems)->put($path.$file_name, fopen($file, 'r+'));
            }

            $response = Self::getFileUrl($file_name, $path);
        }

        return $response;
    }
    // Uploaded file get URL
    public static function getFileUrl($file_name = '', $path = '') {
        $response     = [];

        if (!empty($file_name) && !empty($path)) {
            $response['path'] = Self::generatefileUrl($path, $file_name);
            $response['name'] = $file_name;
        }

        return $response;
    }
    public static function generatefileUrl($path = '', $file_name = '') {
        $filesystems  = config('filesystems.default');
        $timeout      = config('filesystems.cloud_front.timeout');
        $mtimeout     = 30;
        $expires      = ($timeout > 0) ? (time() + $timeout) : (time() + (60 * $mtimeout));
        $file = $url  = '';

        if (!empty($path) && !empty($file_name)) {
            $file = $path.$file_name;
            if ($filesystems === 's3') {
                $keyPairId  = config('filesystems.cloud_front.key_pair_id');
                $key_path   = config('filesystems.cloud_front.pem_path');
                $resource   = config('filesystems.cloud_front.url').'/'.$file;
                $policy     = '{"Statement":[{"Resource":"'. $resource .'","Condition":{"DateLessThan":{"AWS:EpochTime":'. $expires .'}}}]}';
                $priv_key   = file_get_contents($key_path);
                // Create the private key
                $key        = openssl_get_privatekey($priv_key, '');

                if ($key) {
                    // Sign the policy with the private key
                    if (openssl_sign($policy, $signed_policy, $key)) {
                        // Create url safe signed policy
                        $base64_signed_policy = base64_encode($signed_policy);
                        $signature            = strtr($base64_signed_policy, '+=/', '-_~');
                        // Construct the URL (Cloud Front)
                        $url = $resource.'?Expires='.$expires.'&Signature='.$signature.'&Key-Pair-Id='.$keyPairId;

                        // AWS
                        // $url = Storage::disk($filesystems)->temporaryUrl($file, now()->addMinutes(5));
                    } else {
                        $url = ''; // "Failed to sign policy: ".openssl_error_string()
                    }
                } else {
                    $url = ''; // "Failed to load private key!"
                }
            } else {
                if (Storage::disk($filesystems)->exists($file)) {
                    // $base_url   = env('APP_ROOT_PATH', url('/'));
                    // $url_exp_min= 5;
                    // $path       = Storage::disk($filesystems)->url($file);
                    // $path       = Storage::disk($filesystems)->temporaryUrl($file, now()->addSeconds($url_exp_min));
                    // dd($path);
                    // $base_url   = str_replace("/public", "", $base_url);
                    // $url        = $path;

                    // $url        = Storage::disk($filesystems)->url('app/'.$file);
                    // $url        = Storage::disk($filesystems)->temporaryUrl('app/'.$file, now()->addMinutes($url_exp_min));
                    // $url        = $url ? $url : '';
                    $url = config('app.storage_url').$file;
                }
            }
        }
        return $url;
    }
    // Delete Uploaded file
    public static function deleteFile($file_name = '', $path = '') {
        $filesystems= config('filesystems.default');
        $response   = [];

        if (!empty($file_name) && !empty($path)) {
            $response = Storage::disk($filesystems)->delete($path.$file_name);
        }

        return $response;
    }
    // Delete Folder
    public static function deleteFolder($path = '') {
        $filesystems= config('filesystems.default');
        $response   = [];

        if (!empty($path)) {
            $directories = Storage::disk($filesystems)->allDirectories($path);
            if (count($directories) > 0) {
                foreach($directories as $k => $v) {
                    $files = Storage::disk($filesystems)->files($path.$v);
                    if (count($files) > 0) {
                        Storage::disk($filesystems)->delete($files);
                    }
                }
            } else {
                $files = Storage::disk($filesystems)->files($path);
                if (count($files) > 0) {
                    Storage::disk($filesystems)->delete($files);
                }
            }
            sleep(1);
            if (Storage::disk($filesystems)->exists($path)) {
                Storage::disk($filesystems)->deleteDirectory($path);
            }
            return true;
        }

        return $response;
    }

    public static function getFolderFileUrl($module, $ref_id = null, $file_ref_id = null, $contact_ref_id = null, $form_slug = null, $folder = null) {
        $filesystems  = config('filesystems.default');
        $path = '';
        if ($module == 'KEY') {
            $path = self::$client_store_path.$ref_id.'/'.self::$key_store_path.'/'.$file_ref_id.'/';
            if ($folder != null) {
                $path = $path.$folder.'/';
            }
        }
        if ($module == 'HEADER') {
            $path = self::$client_store_path.$ref_id.'/'.self::$template_store_path.'/'.$file_ref_id.'/'.self::$header_store_path;
            if ($folder != null) {
                $path = $path.$folder.'/';
            }
        }
        if ($module == 'PREVIEW') {
            $path = self::$client_store_path.$ref_id.'/'.self::$template_store_path.'/'.$file_ref_id.'/'.self::$template_review_store_path;
            if ($folder != null) {
                $path = $path.$folder.'/';
            }
        }
        $paths = array();
        if ($module == 'FORM' && $folder != null) {
            foreach(Storage::disk($filesystems)->directories($path) as $directory) {
                $k = explode('/', $directory);
                $k = @$k[count($k)-1];
                if ($k) {
                    $paths[$k] = array();
                    foreach(Storage::disk($filesystems)->files($path.'/'.$k) as $file) {
                        $paths[$k][] = config('app.storage_url').$file;
                    }
                }
            }
        }
        if (count($paths) > 0) {
            return $paths;
        }
        foreach(Storage::disk($filesystems)->files($path) as $file) {
            $paths[] = config('app.storage_url').$file;
        }
        return $paths;
    }

    public static function moveFiles($src, $dest) {
        $filesystems = config('filesystems.default');
        if (Storage::exists($src)) {
            Storage::move($src,$dest);
        }
        return true;
    }
}
