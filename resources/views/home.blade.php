<!DOCTYPE html>
<html>
<head>
    <title>Test Tailwind</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="p-8 bg-gray-100">
    <div class="max-w-md mx-auto bg-white rounded-xl shadow-md p-8">
        <h1 class="text-3xl font-bold text-green-600 mb-4">✅ Tailwind Funciona</h1>
        <div class="space-y-4">
            <div class="p-4 bg-blue-500 text-white rounded-lg">bg-blue-500</div>
            <div class="p-4 bg-red-500 text-white rounded-lg">bg-red-500</div>
            <div class="p-4 bg-green-500 text-white rounded-lg">bg-green-500</div>
            <div class="p-4 bg-gradient-to-r from-amber-600 to-yellow-500 text-white rounded-lg">
                Botón dorado de prueba
            </div>
        </div>
    </div>
</body>
</html>
