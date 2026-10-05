<?php
$hash_andrew = '$2y$10$Ot59WwY74N5RRm7Q8FIJtOijJS6R.w5wonJUySNLMyHJ6u7OTW9pm';
$hash_andrewT = '$2y$10$SMppMlaMqEd4Xjh.0bBf3eG4QeKlBVLP4Ba.ApZVEWSv6Zzj87fKa';
echo "andrew: " . (password_verify('123', $hash_andrew) ? "MATCH" : "NO_MATCH") . "\n";
echo "andrewT: " . (password_verify('123', $hash_andrewT) ? "MATCH" : "NO_MATCH") . "\n";
