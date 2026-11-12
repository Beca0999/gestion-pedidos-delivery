@include('errors.layout', [
    'code' => 500,
    'title' => 'Ocurrió un problema',
    'message' => 'No pudimos completar la solicitud. Inténtalo de nuevo más tarde.',
])
