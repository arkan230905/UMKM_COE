<?php

$htaccessContent = '<IfModule mod_rewrite.c>
    RewriteEngine On
    
    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
    
    # Allow access to storage files with proper headers
    RewriteCond %{REQUEST_URI} ^/storage/
    RewriteRule ^ - [L]
</IfModule>

<Files "*">
    Require all granted
</Files>

<FilesMatch "\.(png|jpg|jpeg|gif|pdf|doc|docx|xls|xlsx)$">
    Require all granted
    Header always set Cache-Control "public, max-age=31536000"
    Header always set Expires "access plus 1 year"
</FilesMatch>

# Prevent PHP execution in storage directory
<FilesMatch "\.(php|phtml|php3|php4|php5|pl|py|cgi|sh|exe)$">
    Require all denied
</FilesMatch>';

file_put_contents('public/storage/.htaccess', $htaccessContent);

echo "✅ .htaccess created successfully\n";
echo "📁 Path: public/storage/.htaccess\n";
echo "🌐 Now try accessing the bukti faktur file\n";
