<?php

return [
    'page_title'     => 'Clientes potenciales',
    'statuses_title' => 'Estados',

    'statuses' => [
        'prospect' => 'Prospecto',
        'contact'  => 'Contacto',
        'intake'   => 'Entrevista',
        'dead'     => 'Perdido',
        'lead'     => 'Lead',
    ],

    'status_counts' => [
        'singular' => ':count proyecto',
        'plural'   => ':count proyectos',
    ],

    'filters' => [
        'prospect' => 'Prospecto',
        'contact'  => 'Contacto',
        'intake'   => 'Entrevista',
        'dead'     => 'Perdido',
        'lead'     => 'Lead',
    ],

    'list' => [
        'no_requests'          => 'Todavía no hay solicitudes.',
        'no_results_for_state' => 'No se encontraron resultados para este estado.',
    ],

    'intake_modal' => [
        'title'          => 'Planificar entrevista',
        'date_label'     => 'Fecha',
        'duration_label' => 'Duración',
        'duration_30'    => '30 minutos',
        'duration_45'    => '45 minutos',
        'duration_60'    => '60 minutos',
        'cancel'         => 'Cancelar',
        'confirm'        => 'Confirmar y planificar',
        'confirming'     => 'Confirmando…',
    ],

    'intake_panel' => [
        'duration_prefix' => 'Duración:',
        'duration_suffix' => 'min',
        'mark_done'       => 'Marcar entrevista como completada',
        'delete'          => 'Eliminar',
        'planned_badge'   => 'Programada',
        'completed_badge' => 'Completada',
        'delete_title'    => 'Eliminar entrevista',
        'delete_question' => '¿Seguro que quieres eliminar esta entrevista y volver al estado :status?',
        'delete_yes'      => 'Sí, eliminar',
        'delete_cancel'   => 'Cancelar',
        'today'           => 'Hoy',
        'tomorrow'        => 'Mañana',
    ],

    'intake_questions' => [
        'section_title' => 'Entrevista',
        'notes_title'   => 'Notas',
        'notes_help'    => 'Se guardará con el botón Guardar.',
        'notes_missing' => 'No se encontró campo de notas para esta entrevista.',
        'save'          => 'Guardar',
        'saving'        => 'Guardando...',
        'open_panel_tooltip' => 'Entrevista de admisión abierta',
    ],

    'calls' => [
        'section_title'    => 'Llamadas',
        'new_call_tooltip' => 'Nueva llamada',
        'new_call_title'   => 'Nueva llamada',
        'result_label'     => 'Resultado',
        'result_none'      => 'Sin respuesta',
        'result_spoken'    => 'Hablado',
        'note_label'       => 'Nota',
        'note_placeholder' => 'Escribe una nota...',
        'save'             => 'Guardar',
        'saving'           => 'Guardando...',
        'none'             => 'Todavía no hay llamadas registradas.',
        'called_by'        => 'Llamado por: :name',
        'badge_none'       => 'Llamada: Sin respuesta',
        'badge_spoken'     => 'Llamada: Hablado',
        'result_choose'    => 'Elige resultado',
    ],

    'files' => [
        'section_title'   => 'Archivos',
        'drop_text'       => 'Arrastra archivos aquí o :click para elegir',
        'drop_click'      => 'haz clic',
        'drop_help'       => 'Archivos soportados: imágenes, PDF, Word, Excel, texto, ZIP, etc.',
        'none'            => 'Todavía no hay archivos subidos.',
        'uploading'       => 'Subiendo...',
        'uploaded_on'     => 'Subido el :date',
        'open'            => 'Abrir',
        'delete'          => 'Eliminar',
        'delete_title'    => 'Eliminar archivo',
        'delete_question' => '¿Seguro que quieres eliminar este archivo?',
        'delete_yes'      => 'Sí, eliminar',
        'delete_cancel'   => 'Cancelar',
    ],

    'logbook' => [
        'title'        => 'Registro',
        'empty'        => 'Todavía no hay actividad.',
        'by'           => 'Por: :name',
        'unknown_user' => 'Desconocido',
    ],

    'choices' => [
        'new'     => 'Nuevo sitio web',
        'renew'   => 'Renovar sitio web',
        'default' => 'Solicitud de sitio web',
    ],

    'toast' => [
        'status_intake_only_from_contact'
            => "Error. El estado 'Entrevista' solo está permitido desde 'Contacto' (actual: :current).",
        'status_dead_only_from_contact_or_intake'
            => "Error. El estado 'Perdido' solo está permitido desde 'Contacto' o 'Entrevista' (actual: :current).",
        'status_already'
            => 'Error. Esta solicitud ya tiene el estado :status.',
        'status_update_success'
            => 'Estado actualizado correctamente a :status.',
        'status_update_error'
            => 'No se pudo actualizar el estado.',
        'intake_planned'
            => 'Entrevista planificada y estado actualizado a Entrevista.',
        'intake_plan_error'
            => 'No se pudo planificar la entrevista.',
        'intake_completed'
            => 'Entrevista marcada como completada.',
        'intake_complete_error'
            => 'No se pudo completar la entrevista.',
        'intake_removed'
            => 'Entrevista eliminada y estado devuelto a Contacto.',
        'intake_remove_error'
            => 'No se pudo eliminar la entrevista.',
        'answer_save_success'
            => 'Respuestas guardadas correctamente.',
        'answer_save_error'
            => 'No se pudo guardar la respuesta.',
        'call_save_success'
            => 'Llamada guardada correctamente.',
        'call_save_error'
            => 'No se pudo guardar la llamada.',
        'file_upload_success'
            => 'Archivo añadido correctamente a la solicitud.',
        'file_upload_error'
            => 'No se pudieron subir los archivos.',
        'file_delete_success'
            => 'Archivo eliminado correctamente.',
        'file_delete_error'
            => 'No se pudo eliminar el archivo.',
        'status_lead_requires_intake'
            => "Error. El estado «Lead» solo es posible cuando la reunión de intake se ha marcado como completada.",
    ],

    'lead_modal' => [
        'title'   => 'Convertir en proyecto',
        'text'    => '¿Seguro que quieres convertir esta solicitud en un proyecto?',
        'confirm' => 'Sí, estoy seguro',
        'cancel'  => 'Cancelar',
    ],

    'errors' => [
        'intake_only_from_contact'
            => "Solo puedes pasar a 'Entrevista' cuando el estado actual es 'Contacto'.",
        'lead_requires_intake'
            => "Error. El estado 'Lead' solo es posible cuando la entrevista está marcada como completada.",
    ],
];
