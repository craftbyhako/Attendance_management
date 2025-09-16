<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>勤怠アプリ</title>
    <link rel="stylesheet" href="{{asset('css/sanitize.css')}}">
    <link rel="stylesheet" href="{{asset('css/common.css')}}">
    @yield('css')
</head>

<body>
    <header class="header">
        <div class="header__logo">
            <a href="/login">coachtch
                <!-- <img src="{{ asset('img/logo.png') }}" alt="ロゴ"> -->
            </a>
        </div>
    @if( !in_array(Route::currentRouteName(), ['register', 'login', 'verification.notice']) )
    
        <nav class="header__nav">
            <ul>
                @if(Auth::check())
            
            <!-- ログイン時のリンク先入力 -->
                    <li><a href="">勤怠</a></li> 
                    <li><a href="">勤怠一覧</a></li>
                    <li><a href="">申請</a></li>
                    <li>
                        <form action="{{ route('logout') }}" method="post">
                            @csrf
                            <button class="header__logout">ログアウト</button>
                        </form>
                    </li>
                @else
                <!-- ログインしていないときのリンク先 -->
                    <li><a href="{{ route('login') }}">ログイン</a></li>
                    <li><a href="{{ route('register') }}">会員登録</a></li>

                @endif
            </ul>
        </nav>
        @endif
        @yield('link')
    </header>

    <main>
        @yield('content')
    </main>
</body>
</html>
