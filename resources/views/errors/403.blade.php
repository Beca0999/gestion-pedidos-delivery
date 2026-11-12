@include('errors.layout', [
    'code' => 403,
    'title' => 'Acceso no autorizado',
    'message' => 'Tu cuenta no tiene permiso para ver esta página.',
])
