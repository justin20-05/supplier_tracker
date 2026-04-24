<?php

function excelEscape($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function outputExcelReport(string $filename, string $title, array $sections): void
{
    header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    header('Pragma: public');

    echo "\xEF\xBB\xBF";
    ?>
    <html xmlns:x="urn:schemas-microsoft-com:office:excel">
    <head>
        <meta charset="UTF-8">
        <style>
            table {
                border-collapse: collapse;
                width: 100%;
            }

            th, td {
                border: 1px solid #000;
                padding: 6px;
                vertical-align: top;
            }

            .report-title,
            .section-title {
                font-weight: bold;
            }

            .report-title {
                font-size: 16pt;
            }

            .section-title {
                background: #d9eaf7;
            }

            .label {
                font-weight: bold;
                width: 220px;
            }
        </style>
    </head>
    <body>
        <table>
            <tr>
                <td colspan="2" class="report-title"><?= excelEscape($title) ?></td>
            </tr>
            <tr>
                <td class="label">Generated On</td>
                <td><?= excelEscape(date('F d, Y h:i A')) ?></td>
            </tr>
        </table>
        <br>
        <?php foreach ($sections as $section): ?>
            <table>
                <tr>
                    <td colspan="<?= (int) $section['colspan'] ?>" class="section-title"><?= excelEscape($section['title']) ?></td>
                </tr>
                <?php if (!empty($section['headers'])): ?>
                    <tr>
                        <?php foreach ($section['headers'] as $header): ?>
                            <th><?= excelEscape($header) ?></th>
                        <?php endforeach; ?>
                    </tr>
                <?php endif; ?>
                <?php foreach ($section['rows'] as $row): ?>
                    <tr>
                        <?php foreach ($row as $cell): ?>
                            <td><?= excelEscape($cell) ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </table>
            <br>
        <?php endforeach; ?>
    </body>
    </html>
    <?php
    exit;
}
