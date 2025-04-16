<?php
// エラー出力を抑制
error_reporting(0);
ini_set('display_errors', 0);

// 出力バッファリングを開始
ob_start();

// JSONヘッダーを設定
header('Content-Type: application/json; charset=utf-8');

// アップロードディレクトリの設定
$uploadDir = 'E:/Masuda web/web/trimming/';
if (!file_exists($uploadDir)) {
    if (!mkdir($uploadDir, 0777, true)) {
        echo json_encode([
            'success' => false,
            'message' => 'アップロードディレクトリの作成に失敗しました。'
        ]);
        exit;
    }
}

// ディレクトリの書き込み権限を確認
if (!is_writable($uploadDir)) {
    echo json_encode([
        'success' => false,
        'message' => 'アップロードディレクトリに書き込み権限がありません。'
    ]);
    exit;
}

// エラーメッセージの設定
$errorMessages = [
    UPLOAD_ERR_INI_SIZE => 'ファイルサイズが大きすぎます。',
    UPLOAD_ERR_FORM_SIZE => 'ファイルサイズが大きすぎます。',
    UPLOAD_ERR_PARTIAL => 'ファイルのアップロードが完了しませんでした。',
    UPLOAD_ERR_NO_FILE => 'ファイルが選択されていません。',
    UPLOAD_ERR_NO_TMP_DIR => '一時フォルダが存在しません。',
    UPLOAD_ERR_CANT_WRITE => 'ディスクへの書き込みに失敗しました。',
    UPLOAD_ERR_EXTENSION => 'PHPの拡張モジュールによってアップロードが中断されました。'
];

try {
    // ファイルの存在確認
    if (!isset($_FILES['video'])) {
        throw new Exception('ファイルが送信されていません。');
    }

    $file = $_FILES['video'];
    
    // エラーチェック
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception($errorMessages[$file['error']] ?? '不明なエラーが発生しました。');
    }

    // ファイルタイプのチェック
    $allowedTypes = ['video/mp4', 'video/quicktime', 'video/x-msvideo'];
    if (!in_array($file['type'], $allowedTypes)) {
        throw new Exception('許可されていないファイル形式です。');
    }

    // ファイルサイズのチェック（100MB）
    if ($file['size'] > 100 * 1024 * 1024) {
        throw new Exception('ファイルサイズは100MB以下にしてください。');
    }

    // ファイル名の生成
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '.' . $extension;
    $uploadPath = $uploadDir . $filename;

    // ファイルの移動
    if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
        throw new Exception('ファイルの保存に失敗しました。');
    }

    // 成功レスポンス
    $response = [
        'success' => true,
        'message' => 'アップロードが完了しました。',
        'filename' => $filename,
        'path' => $uploadPath
    ];

} catch (Exception $e) {
    // エラーレスポンス
    $response = [
        'success' => false,
        'message' => $e->getMessage()
    ];
}

// 出力バッファをクリア
ob_end_clean();

// JSONレスポンスを出力
echo json_encode($response);
exit; 