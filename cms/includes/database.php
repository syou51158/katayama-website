<?php
class JsonDatabase {
    private $dataDir = __DIR__ . '/../data/';
    
    public function __construct() {
        if (!is_dir($this->dataDir)) {
            throw new Exception('データディレクトリが見つかりません。');
        }
    }
    
    public function read($table) {
        $file = $this->dataDir . $table . '.json';
        
        if (!file_exists($file)) {
            return [];
        }
        
        $content = file_get_contents($file);
        return json_decode($content, true) ?: [];
    }
    
    public function write($table, $data) {
        $file = $this->dataDir . $table . '.json';
        
        $jsonData = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
        if (file_put_contents($file, $jsonData) === false) {
            throw new Exception('データの保存に失敗しました。');
        }
        
        return true;
    }
    
    public function insert($table, $data) {
        $records = $this->read($table);
        
        // IDを自動生成
        $maxId = 0;
        foreach ($records as $record) {
            if (isset($record['id']) && $record['id'] > $maxId) {
                $maxId = $record['id'];
            }
        }
        
        $data['id'] = $maxId + 1;
        $data['created_at'] = date('c');
        $data['updated_at'] = date('c');
        
        $records[] = $data;
        
        return $this->write($table, $records) ? $data['id'] : false;
    }
    
    public function update($table, $id, $data) {
        $records = $this->read($table);
        
        foreach ($records as &$record) {
            if ($record['id'] == $id) {
                $data['updated_at'] = date('c');
                $record = array_merge($record, $data);
                return $this->write($table, $records);
            }
        }
        
        return false;
    }
    
    public function delete($table, $id) {
        $records = $this->read($table);
        
        $filtered = array_filter($records, function($record) use ($id) {
            return $record['id'] != $id;
        });
        
        return $this->write($table, array_values($filtered));
    }
    
    public function findById($table, $id) {
        $records = $this->read($table);
        
        foreach ($records as $record) {
            if ($record['id'] == $id) {
                return $record;
            }
        }
        
        return null;
    }
    
    public function backup() {
        $backupDir = $this->dataDir . 'backups/';
        
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }
        
        $timestamp = date('Y-m-d_H-i-s');
        $backupFile = $backupDir . 'backup_' . $timestamp . '.json';
        
        $allData = [];
        $files = glob($this->dataDir . '*.json');
        
        foreach ($files as $file) {
            $table = basename($file, '.json');
            $allData[$table] = $this->read($table);
        }
        
        file_put_contents($backupFile, json_encode($allData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        
        return $backupFile;
    }
}
?>
