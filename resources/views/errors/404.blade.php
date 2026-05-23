<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Página No Encontrada</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Montserrat:wght@600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body class="bg-omg-chardon min-h-screen flex items-center justify-center">
    <div class="text-center p-8">
        <div class="bg-white rounded-xl shadow-sm p-10 max-w-md mx-auto">
            <div class="bg-yellow-100 rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-search text-yellow-500 text-4xl"></i>
            </div>
            <h1 class="font-heading font-bold text-omg-nile text-4xl mb-2">404</h1>
            <h2 class="font-heading font-bold text-omg-nile text-xl mb-4">Página No Encontrada</h2>
            <p class="text-omg-slate text-sm mb-6">La página que buscas no existe o fue movida.</p>
            <a href="{{ url('/dashboard') }}"
               class="bg-omg-coral text-white px-6 py-2 rounded-lg hover:opacity-90 font-heading font-semibold inline-flex items-center gap-2">
                <i class="fas fa-home"></i> Volver al Inicio
            </a>
        </div>
    </div>
</body>
</html>