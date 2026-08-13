<?php

/**
 * Validates and saves an uploaded image (profile picture / school logo).
 * Never trusts the browser-supplied filename or extension: the real type is
 * checked via getimagesize(), and the saved filename is generated fresh.
 *
 * @param array  $file    One entry from $_FILES, e.g. $_FILES['avatar'].
 * @param string $destDir Directory to save into, relative to this file's
 *                         directory, e.g. 'uploads/avatars'. Must already exist.
 * @return array{success:bool, path:?string, error:?string} $path is the
 *         relative web path to use in an <img src="">, e.g.
 *         'uploads/avatars/6f1c9…e2.png'.
 */
function handle_image_upload(array $file, string $destDir): array
{
    if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return ['success' => false, 'path' => null, 'error' => 'No file was selected.'];
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'path' => null, 'error' => 'Upload failed. Please try again.'];
    }

    $maxBytes = 2 * 1024 * 1024;
    if (($file['size'] ?? 0) > $maxBytes) {
        return ['success' => false, 'path' => null, 'error' => 'Image must be smaller than 2MB.'];
    }

    $imageInfo = @getimagesize($file['tmp_name']);
    if ($imageInfo === false) {
        return ['success' => false, 'path' => null, 'error' => 'File is not a valid image.'];
    }

    $allowedExtensions = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
    ];
    $mime = $imageInfo['mime'] ?? '';
    if (!isset($allowedExtensions[$mime])) {
        return ['success' => false, 'path' => null, 'error' => 'Only JPG, PNG, GIF, or WEBP images are allowed.'];
    }

    $destDir = rtrim($destDir, '/');
    $absoluteDestDir = __DIR__ . '/' . $destDir;
    if (!is_dir($absoluteDestDir)) {
        return ['success' => false, 'path' => null, 'error' => 'Upload destination is not available.'];
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $allowedExtensions[$mime];
    $absolutePath = $absoluteDestDir . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $absolutePath)) {
        return ['success' => false, 'path' => null, 'error' => 'Could not save the uploaded file.'];
    }

    return ['success' => true, 'path' => $destDir . '/' . $filename, 'error' => null];
}

/**
 * Deletes a previously uploaded file (old avatar/logo being replaced).
 * $relativePath must be a path this app generated (e.g. from
 * handle_image_upload) — never pass raw user input here.
 */
function delete_uploaded_file(?string $relativePath): void
{
    if (!$relativePath) {
        return;
    }
    $absolutePath = __DIR__ . '/' . ltrim($relativePath, '/');
    if (is_file($absolutePath)) {
        @unlink($absolutePath);
    }
}
