<!DOCTYPE html>
<html>
<head><title>Token</title></head>
<body>
    <p>Login successful! Copy this token:</p>
    <pre>{{ $token }}</pre>
    <hr>
    <p>User Info:</p>
    <pre>{{ json_encode($user, JSON_PRETTY_PRINT) }}</pre>
    {{-- The JS part won't run correctly here, but that's okay for getting the token --}}
    <script> /* Original script */ </script>
</body>
</html>