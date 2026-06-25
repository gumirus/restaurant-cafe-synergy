<?php
header('Content-Type: text/plain');
echo "SMTP_USER: " . (getenv('SMTP_USER') ?: 'NOT SET') . "\n";
echo "SMTP_PASSWORD: " . (getenv('SMTP_PASSWORD') ?: 'NOT SET') . "\n";
