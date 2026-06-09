<?php
// =============================================
// ВЫХОД ИЗ СИСТЕМЫ
// =============================================

require_once __DIR__ . '/config/session.php';

session_destroy();
redirect('../frontend/index.php');
