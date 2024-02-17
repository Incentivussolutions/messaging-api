<?php

namespace App\Helpers;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Style;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ExcelCreator extends Spreadsheet {
    public $spreadsheet;
    public $writer;
    public $sheet;
    public $col_names;
    public function __construct() {
        $this->spreadsheet = new Spreadsheet();
        $this->writer = IOFactory::createWriter($this->spreadsheet, 'Xlsx');
        $this->sheet = $this->spreadsheet->getActiveSheet();
        $this->col_names = range('A', 'Z');
    }
    
    public function getBoldStyle() {
        return [
            'font' => [
                'bold' => true,
            ]
        ];
    }

    public function getBoldWithSizeStyle($size) {
        return [
            'font' => [
                'bold' => true,
                'size' => $size
            ]
        ];
    }

    public function getHeaderStyle() {
        return [
            'font' => [
                'bold' => true,
                'size' => '12'
            ],
            'borders' => array(
                'allBorders' => array(
                    'borderStyle' => Border::BORDER_THIN
                )
            ),
        ];
    }

    public function getThinBorderStyle() {
        return [
            'borders' => array(
                'allBorders' => array(
                    'borderStyle' => Border::BORDER_THIN
                )
            ),
        ];
    }

    public function getCellXFValue($cell) {
        return $cell->getXfIndex();
    }

    public function getRowNo($cell) {
        return $cell->getRow();
    }

    public function getColNo($cell) {
        // return (array_search($cell->getColumn(), $this->col_names) + 1);
        return Coordinate::columnIndexFromString($cell->getColumn());
    }

    public function setBoldStyle($cell) {
        $this->sheet->getStyle($cell)->applyFromArray($this->getBoldStyle());
    }

    public function setHeaderStyle($cell) {
        $this->sheet->getStyle($cell)->applyFromArray($this->getHeaderStyle());
    }

    public function setThinBorderStyle($cell) {
        $this->sheet->getStyle($cell)->applyFromArray($this->getThinBorderStyle());
    }

    public function setBoldWithSizeStyle($cell, $size) {
        $this->sheet->getStyle($cell)->applyFromArray($this->getBoldWithSizeStyle($size));
    }
    
    public function setCenterStyle($cell) {
        $this->sheet->getStyle($cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER_CONTINUOUS);
    }

    public function addHeader($col_index, $row_index, $headers, $options = null) {
        if ($options && @$options['with_width']) {
            foreach($headers as $key => $value) {
                if (@$value['width'] > 2) {
                    $start = $this->getCellCoordinate($col_index, $row_index);
                    $end = $this->getCellCoordinate($col_index +@$value['width'], $row_index);
                    $merge_cell = $start.":".$end;
                    $this->sheet->mergeCells($merge_cell);
                    // $this->sheet->getStyle($merge_cell)->getAlignment()->setWrapText(true);
                }
                if(@$value['is_translate'] && @$value['name']) {
                    $value['name'] = __('exports.'.$value['name']);
                }
                $this->insertCellValue($col_index, $row_index, @$value['name']);
                $current_cell = (@$merge_cell) ? $merge_cell : $this->getCellCoordinate($col_index, $row_index);
                $this->setHeaderStyle($current_cell);
                $col_index++;
            }
        } else {
            foreach($headers as $key => $value) {
                if(@$options['is_translate']) {
                    $value = __('exports.'.$value);
                }
                $this->insertCellValue($col_index, $row_index, $value);
                $current_cell = $this->getCellCoordinate($col_index, $row_index);
                $this->setHeaderStyle($current_cell);
                $col_index++;
            }
        }
        $last_cell = $this->getCell($col_index, $row_index);
        return $last_cell;
    }

    public function getCellCoordinate($col_index, $row_index) {
        return $this->sheet->getCellByColumnAndRow($col_index, $row_index)->getCoordinate();
    }

    public function getCell($col_index, $row_index) {
        return $this->sheet->getCellByColumnAndRow($col_index, $row_index);
    }
    public function setAutoSize($col_index, $row_index) {
        $cell = $this->getCell($col_index, $row_index);
        $this->sheet->getColumnDimension($cell->getColumn())->setAutoSize(true);
    }

    public function insertCellValue($col_index, $row_index, $value, $options = array()) {
        if ($options && @$options['is_merge'] && @$options['merge_range']) {
            $start = $this->getCellCoordinate($col_index, $row_index);
            $end = $this->getCellCoordinate($col_index + @$options['merge_range'], $row_index);
            $merge_cell = $start.":".$end;
            $this->sheet->mergeCells($merge_cell);
            $this->setCenterStyle($merge_cell);
        }
        if ($options && @$options['text_wrap']) {
            if ($merge_cell) {
                $cell = $merge_cell;
            } else {
                $cell = $this->getCellCoordinate($col_index, $row_index);
            }
            $this->sheet->getStyle($cell)->getAlignment()->setWrapText(true);
        }
        if ($value !== null) {
            if(@$options['is_translate']) {
                $value = __('exports.'.$value);
            }
            $this->sheet->setCellValueByColumnAndRow($col_index, $row_index, $value);
        }
        $current_cell = $this->getCellCoordinate($col_index, $row_index);
        if (@$merge_cell) {
            $current_cell = $merge_cell;
        } else {
            $current_cell = $this->getCellCoordinate($col_index, $row_index);
        }
        if ($options && @$options['is_bold']) {
            $this->setBoldStyle($current_cell);
        }
        if ($options && @$options['is_header']) {
            $this->setBoldWithSizeStyle($current_cell, 12);
        }
        if ($options && @$options['is_border']) {
            $this->setThinBorderStyle($current_cell);
        }
        if ($options && @$options['is_auto_size']) {
            $this->setAutoSize($col_index, $row_index);
        }
        $last_cell = $this->getCell($col_index, $row_index);
        return $last_cell;
    }
}
?>