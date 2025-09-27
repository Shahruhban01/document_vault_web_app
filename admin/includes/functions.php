<?php
// Shared utility functions for admin panel

if (!function_exists('formatBytes')) {
    function formatBytes($bytes, $precision = 2) {
        if ($bytes === 0 || $bytes === null) return '0 B';
        
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}

if (!function_exists('formatDate')) {
    function formatDate($dateString, $format = 'M d, Y') {
        return date($format, strtotime($dateString));
    }
}

if (!function_exists('formatDateTime')) {
    function formatDateTime($dateString, $format = 'M d, Y H:i') {
        return date($format, strtotime($dateString));
    }
}

if (!function_exists('getFileIcon')) {
    function getFileIcon($mimeType) {
        $iconMap = [
            'application/pdf' => '<i class="fas fa-file-pdf" style="color: #e74c3c;"></i>',
            'image/jpeg' => '<i class="fas fa-file-image" style="color: #3498db;"></i>',
            'image/jpg' => '<i class="fas fa-file-image" style="color: #3498db;"></i>',
            'image/png' => '<i class="fas fa-file-image" style="color: #3498db;"></i>',
            'image/gif' => '<i class="fas fa-file-image" style="color: #3498db;"></i>',
            'text/plain' => '<i class="fas fa-file-alt" style="color: #95a5a6;"></i>',
            'application/msword' => '<i class="fas fa-file-word" style="color: #2980b9;"></i>',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => '<i class="fas fa-file-word" style="color: #2980b9;"></i>',
            'application/vnd.ms-excel' => '<i class="fas fa-file-excel" style="color: #27ae60;"></i>',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => '<i class="fas fa-file-excel" style="color: #27ae60;"></i>',
            'application/zip' => '<i class="fas fa-file-archive" style="color: #9b59b6;"></i>',
        ];
        
        return $iconMap[$mimeType] ?? '<i class="fas fa-file" style="color: #95a5a6;"></i>';
    }
}

if (!function_exists('truncateString')) {
    function truncateString($string, $length = 50, $append = '...') {
        if (strlen($string) <= $length) {
            return $string;
        }
        return substr($string, 0, $length) . $append;
    }
}
?>
