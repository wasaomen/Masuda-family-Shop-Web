<?php
// エラー出力を有効化（デバッグ用）
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 出力バッファリングを開始
ob_start();

// JSONヘッダーを設定
header('Content-Type: application/json; charset=utf-8');

// 入力データの取得
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['url'])) {
    echo json_encode([
        'success' => false,
        'message' => 'URLが指定されていません。'
    ]);
    exit;
}

// YouTube URLの検証
$url = $input['url'];
if (!preg_match('/^(https?:\/\/)?(www\.)?(youtube\.com|youtu\.?be)\/.+$/', $url)) {
    echo json_encode([
        'success' => false,
        'message' => '有効なYouTubeのURLを入力してください。'
    ]);
    exit;
}

// 出力ディレクトリの設定
$outputDir = 'E:/Masuda web/web/trimming/';
if (!file_exists($outputDir)) {
    if (!mkdir($outputDir, 0777, true)) {
        echo json_encode([
            'success' => false,
            'message' => '出力ディレクトリの作成に失敗しました。'
        ]);
        exit;
    }
}

try {
    // 一時ファイル名の生成
    $tempFile = $outputDir . uniqid() . '.mp3';
    
    // yt-dlpコマンドの実行
    $command = sprintf(
        'yt-dlp -x --audio-format mp3 --audio-quality %s -o "%s" "%s" 2>&1',
        escapeshellarg($input['quality']),
        escapeshellarg($tempFile),
        escapeshellarg($url)
    );
    
    exec($command, $output, $returnCode);
    
    if ($returnCode !== 0) {
        throw new Exception('変換に失敗しました。エラー: ' . implode("\n", $output));
    }
    
    // 成功レスポンス
    $response = [
        'success' => true,
        'message' => '変換が完了しました。',
        'downloadUrl' => basename($tempFile)
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