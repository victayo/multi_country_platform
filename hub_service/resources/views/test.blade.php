<!DOCTYPE html>
<html>

<head>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo/dist/echo.iife.js"></script>
    <script src="https://js.pusher.com/8.4.0/pusher.min.js"></script>
</head>

<body>

    <h2>WebSocket Test</h2>

    <script>
        Pusher.logToConsole = true;

        // The CDN IIFE can expose the constructor as Echo.default depending on the build.
        const EchoCtor = window.Echo?.default || window.Echo || window.LaravelEcho;

        if (!EchoCtor) {
            throw new Error('Laravel Echo constructor was not found on window.');
        }

        const echo = new EchoCtor({
            broadcaster: 'pusher',
            key: '1b851ab0ff67113e6fd1',
            cluster: 'eu',
            forceTLS: true,
            enabledTransports: ['ws', 'wss'],
            disableStats: true
        })

        echo.channel('employees.USA')
            .listen('.employee.updated', (event) => {
                console.log('Employee updated:', event)
            })

        echo.channel('checklist.USA')
            .listen('.checklist.updated', (event) => {
                console.log('Checklist updated:', event)
            })
    </script>

</body>

</html>
