<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmailVerificationRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;

class EmailAuthController extends Controller
{
    /**
     * メール認証誘導画面を表示
     */
    public function guide()
    {
        return view('auth.email.guide');
    }
    
    /**
     * MailHogから自動認証機能（開発環境のみ）
     */
    public function autoVerify()
    {
        // 環境変数で制御
        if (!config('app.auto_verify_enabled', false)) {
            abort(404);
        }
        
        // セッションから最新のユーザーIDを取得
        $userId = Session::get('user_id');
        
        if (!$userId) {
            return redirect()->route('email.guide')->with('error', '認証セッションが見つかりません。再度登録してください。');
        }
        
        $user = User::find($userId);
        
        if (!$user) {
            return redirect()->route('email.guide')->with('error', 'ユーザーが見つかりません。');
        }
        
        // 既に認証済みかチェック
        if ($user->email_verified_at) {
            Auth::login($user);
            return redirect()->route('profile.edit')->with('success', '既にメール認証が完了しています。');
        }
        
        try {
            // MailHogのAPIからメール一覧を取得
            $response = Http::timeout(10)->get('http://mailhog:8025/api/v1/messages');
            
            if (!$response->successful()) {
                return redirect()->route('email.guide')->with('error', 'MailHogに接続できませんでした。');
            }
            
            $messages = $response->json();
            
            if (empty($messages)) {
                return redirect()->route('email.guide')->with('error', 'MailHogにメールが見つかりません。');
            }
            
            // デバッグ用：メール構造をログに出力
            $this->ensureLogFilePermissions();
            Log::info('MailHog messages:', $messages);
            
            // 最新の認証メールを検索
            $verificationMessage = null;
            foreach ($messages as $message) {
                // より柔軟な検索条件
                $isCorrectRecipient = false;
                
                // 宛先チェック（複数の形式に対応）
                if (isset($message['To'])) {
                    foreach ($message['To'] as $recipient) {
                        if (is_array($recipient) && isset($recipient['Mailbox']) && isset($recipient['Domain'])) {
                            $recipientEmail = $recipient['Mailbox'] . '@' . $recipient['Domain'];
                            if ($recipientEmail === $user->email) {
                                $isCorrectRecipient = true;
                                break;
                            }
                        } elseif (is_string($recipient) && $recipient === $user->email) {
                            $isCorrectRecipient = true;
                            break;
                        }
                    }
                }
                // ヘッダのToも確認（例: ["user@example.com"]）
                if (!$isCorrectRecipient && isset($message['Content']['Headers']['To'])) {
                    $headersTo = $message['Content']['Headers']['To'];
                    foreach ((array) $headersTo as $toHeader) {
                        if (is_string($toHeader) && strpos($toHeader, $user->email) !== false) {
                            $isCorrectRecipient = true;
                            break;
                        }
                    }
                }
                
                // 件名チェック（MIMEエンコード配列を結合して判定するが、宛先一致だけでもOKとする）
                if ($isCorrectRecipient) {
                    $verificationMessage = $message;
                    break;
                }
            }
            
            if (!$verificationMessage) {
                // より簡単な検索：最新のメールを取得してユーザーのメールアドレスが含まれているかチェック
                $latestMessage = $messages[0] ?? null;
                if ($latestMessage) {
                    $emailContent = $latestMessage['Content']['Body'] ?? '';
                    if (strpos($emailContent, $user->email) !== false && 
                        strpos($emailContent, 'メール認証を完了してください') !== false) {
                        $verificationMessage = $latestMessage;
                    }
                }
            }
            
            if (!$verificationMessage) {
                return redirect()->route('email.guide')->with('error', '認証メールがMailHogに見つかりません。ユーザー: ' . $user->email);
            }
            
            // メール内容から認証コードを抽出
            $emailContent = $verificationMessage['Content']['Body'];
            $verificationCode = $this->extractVerificationCode($emailContent);
            
            if (!$verificationCode) {
                return redirect()->route('email.guide')->with('error', '認証コードを抽出できませんでした。');
            }
            
            // 認証コードの検証
            if ($user->verification_code !== $verificationCode) {
                return redirect()->route('email.guide')->with('error', 'MailHogから取得した認証コードが一致しません。');
            }
            
            // 有効期限チェック
            if ($user->verification_code_expires_at && $user->verification_code_expires_at < now()) {
                return redirect()->route('email.guide')->with('error', '認証コードの有効期限が切れています。');
            }
            
            // メール認証完了
            $user->update([
                'email_verified_at' => now(),
                'verification_code' => null, // コードを無効化
                'verification_code_expires_at' => null,
            ]);
            
            // セッションからuser_idを削除
            Session::forget('user_id');
            
            // 自動ログイン
            Auth::login($user);
            
            return redirect()->route('profile.edit')->with('success', 'MailHogから自動認証が完了しました！');
            
        } catch (\Exception $e) {
            return redirect()->route('email.guide')->with('error', 'MailHogとの通信でエラーが発生しました: ' . $e->getMessage());
        }
    }
    
    /**
     * MIMEヘッダーをデコード
     */
    private function decodeMimeHeader($header)
    {
        // MIMEエンコードされたヘッダーをデコード
        if (preg_match('/=\?utf-8\?Q\?(.+?)\?=/', $header, $matches)) {
            $encoded = $matches[1];
            $decoded = quoted_printable_decode(str_replace('_', ' ', $encoded));
            return $decoded;
        }
        
        return $header;
    }
    
    /**
     * メール内容から認証コードを抽出
     */
    private function extractVerificationCode($emailContent)
    {
        // HTMLメールの場合、HTMLタグを除去
        $textContent = strip_tags($emailContent);
        
        // 6桁の数字を検索
        if (preg_match('/\b(\d{6})\b/', $textContent, $matches)) {
            return $matches[1];
        }
        
        return null;
    }

    /**
     * 認証コード入力画面を表示
     */
    public function showCodeVerificationPage()
    {
        return view('auth.email.verify-code');
    }

    /**
     * 認証コードを検証して認証を完了
     */
    public function verifyCode(Request $request)
    {
        $request->validate([
            'verification_code' => 'required|string|size:6'
        ]);

        $code = $request->input('verification_code');
        
        // セッションから最新のユーザーIDを取得
        $userId = Session::get('user_id');
        
        if (!$userId) {
            return redirect()->route('email.guide')->with('error', '認証セッションが見つかりません。再度登録してください。');
        }
        
        $user = User::find($userId);
        
        if (!$user) {
            return redirect()->route('email.guide')->with('error', 'ユーザーが見つかりません。');
        }
        
        // 既に認証済みかチェック
        if ($user->email_verified_at) {
            Auth::login($user);
            return redirect()->route('profile.edit')->with('success', '既にメール認証が完了しています。');
        }
        
        // 認証コードの検証
        if (!$user->verification_code || $user->verification_code !== $code) {
            return back()->withErrors(['verification_code' => '認証コードが正しくありません。']);
        }
        
        // 有効期限チェック
        if ($user->verification_code_expires_at && $user->verification_code_expires_at < now()) {
            return back()->withErrors(['verification_code' => '認証コードの有効期限が切れています。']);
        }
        
        // メール認証完了
        $user->update([
            'email_verified_at' => now(),
            'verification_code' => null, // コードを無効化
            'verification_code_expires_at' => null,
        ]);
        
        // セッションからuser_idを削除
        Session::forget('user_id');
        
        // 自動ログイン
        Auth::login($user);
        
        return redirect()->route('profile.edit')->with('success', 'メール認証が完了しました！');
    }
    
    /**
     * 認証メールを再送信
     */
    public function resend()
    {
        // セッションから最新のユーザーIDを取得
        $userId = Session::get('user_id');
        
        if (!$userId) {
            return redirect()->route('email.guide')->with('error', '認証セッションが見つかりません。再度登録してください。');
        }
        
        $user = User::find($userId);
        
        if (!$user) {
            return redirect()->route('email.guide')->with('error', 'ユーザーが見つかりません。');
        }
        
        // 既に認証済みかチェック
        if ($user->email_verified_at) {
            Auth::login($user);
            return redirect()->route('profile.edit')->with('success', '既にメール認証が完了しています。');
        }
        
        // 新しい認証コードを生成
        $verificationCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = now()->addHours(24);
        
        $user->update([
            'verification_code' => $verificationCode,
            'verification_code_expires_at' => $expiresAt,
        ]);
        
        // メール送信
        $this->sendVerificationEmail($user);
        
        return redirect()->route('email.guide')->with('success', '認証メールを再送しました。');
    }
    
    /**
     * ログファイルの書き込み権限を確保
     */
    private function ensureLogFilePermissions()
    {
        $logPath = storage_path('logs');
        $logFile = storage_path('logs/laravel.log');
        
        // logsディレクトリが存在しない場合は作成
        if (!file_exists($logPath)) {
            mkdir($logPath, 0777, true);
            chmod($logPath, 0777);
        }
        
        // ログファイルが存在しない場合は作成
        if (!file_exists($logFile)) {
            touch($logFile);
            chmod($logFile, 0777);
        } else {
            // 既存のログファイルの権限を777に設定
            chmod($logFile, 0777);
        }
    }

    /**
     * 新規登録時に認証メールを送信（認証コード方式）
     */
    public function sendVerificationEmail(User $user)
    {
        // 認証コードを生成
        $verificationCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = now()->addHours(24);
        
        $user->update([
            'verification_code' => $verificationCode,
            'verification_code_expires_at' => $expiresAt,
        ]);
        
        // メール送信
        Mail::send('emails.verification', [
            'user' => $user,
            'verificationCode' => $verificationCode
        ], function ($message) use ($user) {
            $message->to($user->email)
                   ->subject('メール認証を完了してください');
        });
    }
}