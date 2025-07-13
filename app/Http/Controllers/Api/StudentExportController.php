<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Carbon\Carbon;

class StudentExportController extends Controller
{
    public function exportToExcel(Request $request)
    {
        try {
            $query = Student::with(['branch', 'guardians']);
            
            // Apply filters if provided
            if ($request->has('branch_id')) {
                $query->where('branch_id', $request->branch_id);
            }
            
            if ($request->has('gender') && $request->gender !== null) {
                $query->where('gender', $request->gender);
            }
            
            if ($request->has('is_present')&& $request->is_present !== null) {
               if($request->is_present==1)
                    $query->where('is_present', true);
                else
                    $query->where('is_present',false);
            }
            if ($request->has('date_from') && $request->has('date_to')&&$request->date_from!==null &&$request->date_to!==null) {
                $query->whereBetween('updated_at', [
                    $request->date_from,
                    $request->date_to
                ]);
            }
            
            $students = $query->get();
            
            // Create spreadsheet
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Set RTL direction for Arabic support
            $sheet->setRightToLeft(true);
            
            // Set sheet title
            $sheet->setTitle('كشف حضور الطلاب');
            
            // Create header
            $this->createHeader($sheet);
            
            // Add student data
            $this->addStudentData($sheet, $students);
            
            // Add statistics summary
            $this->addStatisticsSummary($sheet, $students);
            
            // Apply styling
            $this->applyStyles($sheet, count($students));
            
            // Generate filename
            $filename = 'students_attendance_' . Carbon::now()->format('Y-m-d_H-i-s') . '.xlsx';
            
            // Create writer and save to temporary file
            $writer = new Xlsx($spreadsheet);
            $tempFile = tempnam(sys_get_temp_dir(), 'excel_export_');
            $writer->save($tempFile);
            
            // Return file as download
            return Response::download($tempFile, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ])->deleteFileAfterSend(true);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل في تصدير البيانات: ' . $e->getMessage()
            ], 500);
        }
    }
    
    private function createHeader($sheet)
    {
        // Main title
        $sheet->setCellValue('A1', 'تقرير حضور الطلاب - نظام إدارة المدرسة');
        $sheet->mergeCells('A1:I1');
        
        // Date and time
        $sheet->setCellValue('A2', 'تاريخ التصدير: ' . Carbon::now()->format('Y/m/d H:i:s'));
        $sheet->mergeCells('A2:I2');
        
        // Column headers
        $headers = [
            'A4' => 'رقم الطالب',
            'B4' => 'الاسم بالعربية',
            'C4' => 'الاسم بالإنجليزية',
            'D4' => 'الجنس',
            'E4' => 'الفرع',
            'F4' => 'حالة الحضور',
            'G4' => 'آخر حضور',
            'H4' => 'ولي الأمر',
            'I4' => 'ملاحظات'
        ];
        
        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }
    }
    
    private function addStudentData($sheet, $students)
    {
        $row = 5; // Start after headers
        
        foreach ($students as $student) {
            $sheet->setCellValue('A' . $row, $student->student_number ?? 'غير محدد');
            $sheet->setCellValue('B' . $row, $student->name_ar);
            $sheet->setCellValue('C' . $row, $student->name_en ?? '');
            $sheet->setCellValue('D' . $row, $student->gender === 'male' ? 'ذكر' : 'أنثى');
            $sheet->setCellValue('E' . $row, $student->branch->name_ar ?? 'غير محدد');
            
            // Attendance status
            $attendanceStatus = $student->is_present ? 'حاضر' : 'غائب';
            $sheet->setCellValue('F' . $row, $attendanceStatus);
            
            // Last attendance
            $lastAttendance = $student->last_attendance_at 
                ? Carbon::parse($student->last_attendance_at)->format('Y/m/d H:i')
                : 'لم يسجل';
            $sheet->setCellValue('G' . $row, $lastAttendance);
            
            // Guardian name
            $guardianName = $student->guardians->first()->name_ar ?? 'غير محدد';
            $sheet->setCellValue('H' . $row, $guardianName);
            
            // Notes
            $notes = $student->notes ?? '';
            $sheet->setCellValue('I' . $row, $notes);
            
            $row++;
        }
    }
    
    private function addStatisticsSummary($sheet, $students)
    {
        $totalStudents = $students->count();
        $presentStudents = $students->where('is_present', true)->count();
        $absentStudents = $totalStudents - $presentStudents;
        $maleStudents = $students->where('gender', 'male')->count();
        $femaleStudents = $students->where('gender', 'female')->count();
        
        $startRow = $students->count() + 7;
        
        // Statistics title
        $sheet->setCellValue('A' . $startRow, 'إحصائيات الحضور');
        $sheet->mergeCells('A' . $startRow . ':C' . $startRow);
        $startRow++;
        
        // Statistics data
        $stats = [
            'إجمالي الطلاب: ' . $totalStudents,
            'الحاضرون: ' . $presentStudents,
            'الغائبون: ' . $absentStudents,
            'الذكور: ' . $maleStudents,
            'الإناث: ' . $femaleStudents,
            'نسبة الحضور: ' . ($totalStudents > 0 ? round(($presentStudents / $totalStudents) * 100, 2) : 0) . '%'
        ];
        
        foreach ($stats as $stat) {
            $sheet->setCellValue('A' . $startRow, $stat);
            $startRow++;
        }
    }
    
    private function applyStyles($sheet, $studentCount)
    {
        // Title styling
        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
                'color' => ['rgb' => '2196F3']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);
        
        // Date styling
        $sheet->getStyle('A2')->applyFromArray([
            'font' => [
                'italic' => true,
                'size' => 12,
                'color' => ['rgb' => '666666']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER
            ]
        ]);
        
        // Header styling
        $headerRange = 'A4:I4';
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => '2196F3']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ]);
        
        // Data styling
        if ($studentCount > 0) {
            $dataRange = 'A5:I' . (4 + $studentCount);
            $sheet->getStyle($dataRange)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'CCCCCC']
                    ]
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ]);
            
            // Alternate row colors
            for ($row = 5; $row <= 4 + $studentCount; $row++) {
                if (($row - 5) % 2 == 1) {
                    $sheet->getStyle('A' . $row . ':I' . $row)->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'color' => ['rgb' => 'F5F5F5']
                        ]
                    ]);
                }
            }
        }
        
        // Auto-size columns
        foreach (range('A', 'I') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
        
        // Set row heights
        $sheet->getRowDimension(1)->setRowHeight(25);
        $sheet->getRowDimension(4)->setRowHeight(20);
    }
    
    public function getExportStatistics(Request $request)
    {
        try {
            $query = Student::query();
            
            // Apply filters if provided
            if ($request->has('branch_id')) {
                $query->where('branch_id', $request->branch_id);
            }
            
            if ($request->has('date_from') && $request->has('date_to')) {
                $query->whereBetween('updated_at', [
                    $request->date_from,
                    $request->date_to
                ]);
            }
            
            $students = $query->get();
            $totalStudents = $students->count();
            $presentStudents = $students->where('is_present', true)->count();
            $absentStudents = $totalStudents - $presentStudents;
            
            return response()->json([
                'success' => true,
                'statistics' => [
                    'total_students' => $totalStudents,
                    'present_students' => $presentStudents,
                    'absent_students' => $absentStudents,
                    'attendance_rate' => $totalStudents > 0 ? round(($presentStudents / $totalStudents) * 100, 2) : 0,
                    'export_date' => Carbon::now()->format('Y-m-d H:i:s'),
                    'branches' => Branch::withCount('students')->get()
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل في جلب الإحصائيات: ' . $e->getMessage()
            ], 500);
        }
    }
}