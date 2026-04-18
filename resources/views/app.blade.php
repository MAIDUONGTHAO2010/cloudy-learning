<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cloudy Learning</title>
    @vite(['resources/css/app.css', 'resources/app/app.ts'])
</head>
<body>
    <div id="app"></div>
</body>
</html>
