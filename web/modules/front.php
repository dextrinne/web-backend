<?php
function front_get($request)
{
    global $conf;
    session_start();

    // Получаем ошибки и сообщения из сессии, если есть
    $messages = isset($_SESSION['form_messages']) ? $_SESSION['form_messages'] : [];
    $errors = isset($_SESSION['form_errors']) ? $_SESSION['form_errors'] : [];

    // Очищаем сессию
    unset($_SESSION['form_messages']);
    unset($_SESSION['form_errors']);

    $data = [
        'frontend_dir' => conf('frontend_dir'),
        'base_url' => conf('basedir'),
        'messages' => $messages, // Передаем сообщения в шаблон
        'errors' => $errors, // Передаем ошибки в шаблон
        // Передаем информацию о том, что это главная страница
        'is_home' => true
    ];

    // Собираем HTML страницы
    ob_start();
    include_frontend_file('header.html', $data);
    include_frontend_file('index.html', $data);
    include_frontend_file('sections.html', $data);

    include_frontend_file('footer.html', $data);
    $html = ob_get_clean();

    return $html;
}

function front_post($request)
{
    //Обрабатываем POST-запрос для формы регистрации
    if (isset($request['post']['fio'])) {
        require_once(conf('form_module') . '.php');
        return form_reg_post($request);
    } else {
        // Если форма отправлена без данных, возвращаем обратно на форму с ошибкой
        $_SESSION['form_errors'] = ['common' => 'Пожалуйста, заполните форму.']; // Сообщение об ошибке
        return redirect('register'); // Перенаправляем на страницу регистрации
    }
    //Обработка POST запросов для других форм, если они есть на главной странице
}
?>