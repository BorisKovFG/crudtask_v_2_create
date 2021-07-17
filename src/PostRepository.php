<?php

namespace App;

class PostRepository
{
    const FILE = __DIR__ . '/../db/db.txt';

    public function save(array $data)
    {
        $data = json_encode($data, JSON_PRETTY_PRINT);
        file_put_contents(self::FILE, $data);
    }
    public function read($file = self::FILE)
    {
        $data = file_get_contents($file);
        return json_decode($data, true);
    }

}
