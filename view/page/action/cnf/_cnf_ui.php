<?php
/**
 * Layout padrão das telas CNF (mesmo padrão visual do cadastro de usuários).
 */

function cnf_page_open(string $title, string $subtitle = ''): void
{
    echo '<div class="cnf-page">';
    echo '<header class="cnf-header"><div>';
    echo '<h5 class="cnf-title">' . htmlspecialchars($title) . '</h5>';
    if ($subtitle !== '') {
        echo '<p class="cnf-sub">' . htmlspecialchars($subtitle) . '</p>';
    }
    echo '</div>';
}

function cnf_page_actions(string $html): void
{
    echo '<div class="cnf-actions">' . $html . '</div></header>';
}

function cnf_page_header_close(): void
{
    echo '</header>';
}

function cnf_table_wrap_open(): void
{
    echo '<div class="cnf-table-wrap">';
}

function cnf_table_wrap_close(): void
{
    echo '</div></div>';
}

function cnf_form_section_open(string $title): void
{
    echo '<div class="st-form-section cnf-form-section">';
    echo '<h6 class="st-form-section-title cnf-form-section-title">' . htmlspecialchars($title) . '</h6>';
    echo '<div class="st-form-grid cnf-form-grid">';
}

function cnf_form_section_close(): void
{
    echo '</div></div>';
}

function cnf_form_full_open(): void
{
    echo '<div class="st-form-grid st-form-grid--1 cnf-form-grid--full">';
}

function cnf_form_full_close(): void
{
    echo '</div>';
}

function cnf_field_label(string $for, string $label, bool $required = false): string
{
    $req = $required ? ' <span class="st-required">*</span>' : '';
    return '<label class="st-label" for="' . htmlspecialchars($for) . '">' . htmlspecialchars($label) . $req . '</label>';
}

function cnf_field_input(string $id, string $label, array $opts = []): void
{
    $type = $opts['type'] ?? 'text';
    $required = !empty($opts['required']);
    $disabled = !empty($opts['disabled']) ? ' disabled' : '';
    $value = isset($opts['value']) ? ' value="' . htmlspecialchars((string) $opts['value']) . '"' : '';
    $extra = $opts['extra'] ?? '';
    $reqAttr = $required ? ' pattern=".+" required' : '';

    echo '<div class="st-field input-container">';
    echo cnf_field_label($id, $label, $required);
    echo '<input id="' . htmlspecialchars($id) . '" class="input" type="' . htmlspecialchars($type) . '"' . $value . $disabled . $reqAttr;
    if ($extra !== '') {
        echo ' ' . $extra;
    }
    echo ' />';
    echo '</div>';
}

function cnf_field_select(string $id, string $label, string $optionsHtml, array $opts = []): void
{
    $required = !empty($opts['required']);
    $disabled = !empty($opts['disabled']) ? ' disabled' : '';
    $multiple = !empty($opts['multiple']) ? ' multiple' : '';
    $name = $opts['name'] ?? $id;
    $class = 'form-control' . ($multiple ? '' : '');
    $selectClass = !empty($opts['multiple']) ? ' class="' . $class . '"' : '';

    echo '<div class="st-field input-container">';
    echo cnf_field_label($id, $label, $required);
    echo '<select name="' . htmlspecialchars($name) . '" id="' . htmlspecialchars($id) . '"' . $selectClass . $disabled . $multiple . '>';
    echo $optionsHtml;
    echo '</select>';
    echo '</div>';
}

function cnf_field_textarea(string $id, string $label, array $opts = []): void
{
    $rows = (int) ($opts['rows'] ?? 6);
    $value = $opts['value'] ?? '';
    $disabled = !empty($opts['disabled']) ? ' disabled' : '';
    $full = !empty($opts['full']);

    echo '<div class="st-field input-container cnf-field-full' . ($full ? ' cnf-field-editor' : '') . '">';
    echo cnf_field_label($id, $label, !empty($opts['required']));
    echo '<textarea id="' . htmlspecialchars($id) . '" class="input" rows="' . $rows . '"' . $disabled . '>';
    echo htmlspecialchars((string) $value);
    echo '</textarea>';
    echo '</div>';
}

function cnf_field_switch(string $id, string $label, bool $checked = false): void
{
    $chk = $checked ? ' checked' : '';
    echo '<div class="st-field cnf-switch-field">';
    echo '<span class="st-label">' . htmlspecialchars($label) . '</span>';
    echo '<div class="cnf-switch-wrap"><div class="switch">';
    echo '<input type="checkbox" id="' . htmlspecialchars($id) . '"' . $chk . '>';
    echo '<label for="' . htmlspecialchars($id) . '"></label>';
    echo '</div><span class="cnf-switch-hint">Ativo</span></div>';
    echo '</div>';
}

function cnf_modal_shell_open(string $modalId, string $title, string $size = 'lg'): void
{
    $dialogClass = 'modal-dialog modal-dialog-centered modal-dialog-scrollable';
    if ($size === 'lg') {
        $dialogClass .= ' modal-lg';
    } elseif ($size === 'xl') {
        $dialogClass .= ' modal-xl';
    }
    echo '<div class="modal fade" id="' . htmlspecialchars($modalId) . '" tabindex="-1" aria-hidden="true">';
    echo '<div class="' . $dialogClass . '">';
    echo '<div class="modal-content cnf-modal">';
    echo '<div class="modal-header">';
    echo '<h5 class="modal-title">' . $title . '</h5>';
    echo '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>';
    echo '</div>';
    echo '<div class="modal-body cnf-form st-form">';
}

function cnf_modal_shell_close(string $feedId, string $saveId = 'save', bool $showSave = true): void
{
    echo '</div>';
    echo '<div class="modal-footer cnf-modal-footer">';
    echo '<div id="' . htmlspecialchars($feedId) . '" class="cnf-feed"></div>';
    echo '<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><i class="fas fa-times"></i> Cancelar</button>';
    if ($showSave) {
        echo '<button type="button" id="' . htmlspecialchars($saveId) . '" class="btn btn-solvetask"><i class="fas fa-save"></i> Salvar</button>';
    }
    echo '</div></div></div></div>';
}

function cnf_status_icon(int $ativo): string
{
    return ($ativo == 1)
        ? '<span class="cnf-status cnf-status--ok" title="Ativo"><i class="fas fa-check"></i></span>'
        : '<span class="cnf-status cnf-status--off" title="Inativo"><i class="fas fa-times"></i></span>';
}

/** Célula de situação com data-order para ordenação no DataTables. */
function cnf_status_cell(int $ativo, string $class = 'text-center'): string
{
    return '<td class="' . htmlspecialchars($class) . '" data-order="' . (int) $ativo . '">' . cnf_status_icon($ativo) . '</td>';
}

/** Ordenação SQL padrão CNF: ativos primeiro, depois nome alfabético. */
function cnf_sql_order_ativo_nome(string $nomeCol): string
{
    return ' order by ativo desc, ' . $nomeCol . ' asc';
}

/** Ordenação DataTables padrão CNF (índices 0-based). */
function cnf_datatable_order_ativo_nome(int $statusCol, int $nomeCol = 0): string
{
    return '[[' . $statusCol . ', "desc"], [' . $nomeCol . ', "asc"]]';
}

function cnf_action_icon(string $targetModal): string
{
    return '<button type="button" class="cnf-icon-btn" data-bs-toggle="modal" data-bs-target="#' . htmlspecialchars($targetModal) . '" title="Editar"><i class="fas fa-pen"></i></button>';
}

/**
 * Valor legível para campos somente leitura (vazio → traço).
 *
 * @param mixed $value
 */
function st_display_val($value, string $empty = '—'): string
{
    $text = trim((string) ($value ?? ''));
    if ($text === '' || strcasecmp($text, 'null') === 0 || $text === '00/00/0000' || $text === '0000-00-00') {
        return $empty;
    }
    return $text;
}

/** Alias para telas idx / usu (mesmo layout CNF). */
function st_page_open(string $title, string $subtitle = ''): void
{
    cnf_page_open($title, $subtitle);
}

function st_page_actions(string $html): void
{
    cnf_page_actions($html);
}

function st_page_header_close(): void
{
    cnf_page_header_close();
}

function st_page_close(): void
{
    echo '</div>';
}

function st_filter_bar_open(): void
{
    echo '<div class="cnf-filter-bar st-form st-filter-grid">';
}

function st_filter_bar_close(): void
{
    echo '</div>';
}

function st_panel(string $id, string $class = 'cnf-report-panel'): void
{
    echo '<div id="' . htmlspecialchars($id) . '" class="' . htmlspecialchars($class) . '"></div>';
}

function st_modal_open(string $modalId, string $title, string $size = 'lg'): void
{
    cnf_modal_shell_open($modalId, $title, $size);
}

function st_modal_close(string $feedId, string $saveId = 'save', bool $showSave = true, string $saveIcon = 'fa-save', string $saveLabel = 'Salvar'): void
{
    echo '</div>';
    echo '<div class="modal-footer cnf-modal-footer">';
    echo '<div id="' . htmlspecialchars($feedId) . '" class="cnf-feed"></div>';
    echo '<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><i class="fas fa-times"></i> Cancelar</button>';
    if ($showSave) {
        echo '<button type="button" id="' . htmlspecialchars($saveId) . '" class="btn btn-solvetask"><i class="fas ' . htmlspecialchars($saveIcon) . '"></i> ' . htmlspecialchars($saveLabel) . '</button>';
    }
    echo '</div></div></div></div>';
}

/** Loader HTML para abertura de chat (espelha st-chat-open.js). */
function st_chat_open_loader_html(string $title = 'Abrindo chat', string $subtitle = 'Preparando sua conversa...'): string
{
    return '<div class="st-chat-open" role="status" aria-live="polite">'
        . '<div class="st-chat-open__panel">'
        . '<div class="st-chat-open__spinner" aria-hidden="true"></div>'
        . '<p class="st-chat-open__title">' . htmlspecialchars($title) . '</p>'
        . '<p class="st-chat-open__sub">' . htmlspecialchars($subtitle) . '</p>'
        . '</div></div>';
}

function cnf_datatable_init(string $tableId = 'tabela', array $opts = []): string
{
    $scrollY = $opts['scrollY'] ?? 'calc(var(--action-workspace-min-h) - 180px)';
    $paging = !empty($opts['paging']) ? 'true' : 'false';
    $order = $opts['order'] ?? '[]';
    $buttons = $opts['buttons'] ?? "['excel']";
    $columnDefs = isset($opts['columnDefs']) ? "\n        columnDefs: {$opts['columnDefs']}," : '';

    return <<<JS
<script>
$(function() {
    var initToken = (typeof stCnfDataTableInitToken !== 'undefined') ? stCnfDataTableInitToken : 0;
    var runInit = function() {
        if (initToken !== stCnfDataTableInitToken) {
            return;
        }
        if (typeof stPurgeOrphanDataTables === 'function') {
            stPurgeOrphanDataTables();
        }
        var \$table = $('#action-page #{$tableId}');
        if (!\$table.length) {
            return;
        }
        if (typeof stSafeDestroyDataTable === 'function') {
            stSafeDestroyDataTable(\$table);
        } else if (\$.fn.DataTable.isDataTable(\$table[0])) {
            try {
                \$table.DataTable().clear().destroy(false);
            } catch (e) { /* ignore */ }
        }
        if (\$.fn.DataTable.isDataTable(\$table[0])) {
            return;
        }
        \$table.DataTable({
            dom: '<"cnf-dt-top"lfB>rt<"cnf-dt-bottom"ip>',
            scrollY: '{$scrollY}',
            scrollCollapse: true,
            deferRender: true,
            processing: true,
            paging: {$paging},{$columnDefs}
            order: {$order},
            language: { url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Portuguese-Brasil.json' },
            buttons: { buttons: {$buttons} }
        });
    };
    if (typeof stCnfDataTableInitTimer !== 'undefined' && stCnfDataTableInitTimer) {
        clearTimeout(stCnfDataTableInitTimer);
    }
    stCnfDataTableInitTimer = setTimeout(runInit, 0);
});
</script>
JS;
}
