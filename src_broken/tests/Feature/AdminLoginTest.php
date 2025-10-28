<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;


class AdminLoginTest extends TestCase
{
   
    use RefreshDatabase;
    
    //ログイン--メアドバリデーション
    public function test_admin_login_validate_email()
    {
         // 管理者を作成
        User::factory()->create([
            'user_name' => '管理者',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'admin_role' => 1,
        ]);
        
        $response = $this->post('/admin/login', [
            'email' => "",
            'password' => "password",
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('email');

        $errors = session('errors');
        $this->assertEquals('メールアドレスを入力してください', $errors->first('email'));
    }

    //ログイン--パスワードバリデーション
    public function test_admin_login_validate_password()
    {
        // 管理者を作成
        User::factory()->create([
            'user_name' => '管理者',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'admin_role' => 1,
        ]);

        $response = $this->post('/admin/login', [
            'email' => "general2@gmail.com",
            'password' => "",
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('password');

        $errors = session('errors');
        $this->assertEquals('パスワードを入力してください', $errors->first('password'));
    }

    //ログイン--不一致
    public function test_admin_login_validate_user()
    {
        // 管理者を作成
        User::factory()->create([
            'user_name' => '管理者',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'admin_role' => 1,
        ]);

        $response = $this->post('/admin/login', [
            'email' => "general2@gmail.com",
            'password' => "password123",
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('email');

        $errors = session('errors');
        $this->assertEquals('ログイン情報が登録されていません', $errors->first('email'));
    }
}
