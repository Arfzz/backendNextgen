<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Mentor;
use Illuminate\Support\Facades\Hash;

$mentors = Mentor::all();
foreach($mentors as $m) {
    if(empty($m->username)) {
        $name = strtolower(str_replace(' ', '', $m->nama_mentor));
        $m->username = $name . rand(10,99);
        $m->email = $name . '@example.com';
        $m->password = Hash::make('password123');
        $m->save();
        echo "Updated mentor: " . $m->nama_mentor . "\n";
    }
}
