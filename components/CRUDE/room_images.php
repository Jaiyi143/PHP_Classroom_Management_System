<?php
declare(strict_types=1);

function roomImageDirectory(): string {
    return __DIR__ . '/../../uploads/rooms';
}

function roomImageUrl(int $id): ?string {
    foreach (['jpg', 'png', 'webp'] as $extension) {
        $path = roomImageDirectory() . '/' . $id . '.' . $extension;
        if (is_file($path)) {
            return 'uploads/rooms/' . $id . '.' . $extension . '?v=' . filemtime($path);
        }
    }
    return null;
}

function roomImageUploadError(?array $file): string {
    if ($file === null || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return '';
    }
    if (($file['error'] ?? null) !== UPLOAD_ERR_OK || !is_string($file['tmp_name'] ?? null)) {
        return 'The image could not be uploaded.';
    }
    if (($file['size'] ?? 0) < 1 || $file['size'] > 5 * 1024 * 1024) {
        return 'Choose a JPG, PNG or WebP image smaller than 5 MB.';
    }
    $image = @getimagesize($file['tmp_name']);
    if (!is_uploaded_file($file['tmp_name']) || $image === false
        || !in_array($image[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], true)) {
        return 'Choose a valid JPG, PNG or WebP image.';
    }
    return '';
}

function saveRoomImage(int $id, ?array $file): bool {
    if ($file === null || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return true;
    }
    $type = getimagesize($file['tmp_name'])[2];
    $extension = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp'][$type];
    $directory = roomImageDirectory();
    if (!is_dir($directory) && !mkdir($directory, 0755, true)) {
        return false;
    }
    if (!move_uploaded_file($file['tmp_name'], $directory . '/' . $id . '.' . $extension)) {
        return false;
    }
    foreach (['jpg', 'png', 'webp'] as $oldExtension) {
        if ($oldExtension !== $extension) {
            $oldPath = $directory . '/' . $id . '.' . $oldExtension;
            if (is_file($oldPath)) {
                unlink($oldPath);
            }
        }
    }
    return true;
}

function deleteRoomImage(int $id): void {
    foreach (['jpg', 'png', 'webp'] as $extension) {
        $path = roomImageDirectory() . '/' . $id . '.' . $extension;
        if (is_file($path)) {
            unlink($path);
        }
    }
}
