<?php

function formatFecha($fecha) {
    if (empty($fecha)) return 'Sin fecha';
    
    $fecha_obj = new DateTime($fecha);
    $hoy = new DateTime();
    $ayer = new DateTime('yesterday');
    
    if ($fecha_obj->format('Y-m-d') === $hoy->format('Y-m-d')) {
        return 'Hoy, ' . $fecha_obj->format('H:i');
    } elseif ($fecha_obj->format('Y-m-d') === $ayer->format('Y-m-d')) {
        return 'Ayer, ' . $fecha_obj->format('H:i');
    } else {
        return $fecha_obj->format('d/m/Y H:i');
    }
}

function getActivityIcon($estado) {
    switch ($estado) {
        case 'pendiente': return 'fas fa-clock text-warning';
        case 'tramite': return 'fas fa-tasks text-primary';
        case 'completado': return 'fas fa-check-circle text-success';
        case 'denegado': return 'fas fa-times-circle text-danger';
        default: return 'fas fa-file-alt text-info';
    }
}
?>