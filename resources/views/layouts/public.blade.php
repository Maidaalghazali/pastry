<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kitchen System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" href="{{ asset('img/logo-elaia.jpg') }}" type="image/jpeg">
</head>
<body>
    <nav class="bg-white border-b p-4">
        <div class="container mx-auto flex justify-between">
            <a href="{{ route('items.index') }}" class="text-xl font-bold">🍽️ Kitchen</a>
            @auth
                <div>
                    <span>{{ Auth::user()->name }} ({{ Auth::user()->role }})</span>
                    <form method="POST" action="{{ route('logout') }}" class="inline ml-4">
                        @csrf
                        <button type="submit" class="underline">Logout</button>
                    </form>
                </div>
            @endauth
        </div>
    </nav>

    <main class="container mx-auto py-8">
        @yield('content')
    </main>
</body>
</html>
