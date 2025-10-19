<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;


class RegisterTest extends TestCase
{
    //会員情報登録--名前バリデーション
    public function test_register_user_validate_name()
    {
        $response = $this->post('/register', [
            'name' => "",
            'email' => "test@gmail.com",
            'password' => "password",
            'password_confirmation' => "password",
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('name');

        $errors = session('errors');
        $this->assertEquals('名前を入力してください', $errors->first('name'));
    }

        //会員情報登録--メアドバリデーション
    public function test_register_user_validate_email()
    {
        $response = $this->post('/register', [
            'name' => "テストユーザ",
            'email' => "",
            'password' => "password",
            'password_confirmation' => "password",
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('email');

        $errors = session('errors');
        $this->assertEquals('メールアドレスを入力してください', $errors->first('email'));
    }

    //会員情報登録--パスワードバリデーション
    public function test_register_user_validate_password()
    {
        $response = $this->post('/register', [
            'name' => "テストユーザ",
            'email' => "test@gmail.com",
            'password' => "",
            'password_confirmation' => "password",
        ]);
    }

     //会員情報登録--パスワード7文字以下
    public function test_register_user_validate_password_under7()
    {
        $response = $this->post('/register', [
            'name' => "テストユーザ",
            'email' => "test@gmail.com",
            'password' => "passwor",
            'password_confirmation' => "password",
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('password');

        $errors = session('errors');
        $this->assertEquals('パスワードは8文字以上で入力してください', $errors->first('password'));
    }

    //会員情報登録--パスワード不一致
    public function test_register_user_validate_confirm_password()
    {
        $response = $this->post('/register', [
            'name' => "テストユーザ",
            'email' => "test@gmail.com",
            'password' => "password",
            'password_confirmation' => "password123",
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('password');

        $errors = session('errors');
        $this->assertEquals('パスワードと一致しません', $errors->first('password'));
    }

    //会員情報登録
    public function test_register_user()
    {
        $response = $this->post('/register', [
            'name' => "テストユーザ",
            'email' => "test@gmail.com",
            'password' => "password",
            'password_confirmation' => "password",
        ]);

        $response->assertRedirect('/email/verify');
        $this->assertDatabaseHas(User::class, [
            'name' => "テストユーザ",
            'email' => "test@gmail.com",
        ]);
    }
}

