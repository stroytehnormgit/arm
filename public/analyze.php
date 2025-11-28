<?php
header('Content-Type: text/html; charset=utf-8');

$docxPath = __DIR__ . '/perechen.docx';
if (!file_exists($docxPath)) {
    die("Файл не найден: $docxPath\n");
}

$zip = new ZipArchive;
if ($zip->open($docxPath) === TRUE) {
    // Извлекаем ключевые файлы
    $documentXml = $zip->getFromName('word/document.xml');
    $settingsXml = $zip->getFromName('word/settings.xml');
    $stylesXml = $zip->getFromName('word/styles.xml');
    
    echo "<html><head><meta charset='utf-8'><title>Анализ perechen.docx</title></head><body>";
    echo "<h1>Анализ параметров документа perechen.docx</h1>";
    echo "<pre style='background: #f5f5f5; padding: 20px; border: 1px solid #ddd;'>";
    
    echo "=== WORD/SETTINGS.XML ===\n";
    echo htmlspecialchars($settingsXml) . "\n\n";
    
    // Ищем настройки страницы в document.xml
    if (preg_match('/<w:sectPr[^>]*>.*?<\/w:sectPr>/s', $documentXml, $matches)) {
        echo "=== НАСТРОЙКИ СЕКЦИИ (страницы) ===\n";
        echo htmlspecialchars($matches[0]) . "\n\n";
        
        // Извлекаем размер страницы
        if (preg_match('/<w:pgSz\s+([^>]*)>/', $matches[0], $pgSz)) {
            echo "--- РАЗМЕР СТРАНИЦЫ (w:pgSz) ---\n";
            echo htmlspecialchars($pgSz[0]) . "\n";
            
            // Парсим атрибуты
            if (preg_match('/w:w="(\d+)"/', $pgSz[1], $w)) {
                $widthTwips = intval($w[1]);
                $widthCm = $widthTwips / 567; // 1 twip = 1/567 cm
                echo "Ширина: {$widthTwips} twips = " . round($widthCm, 2) . " см\n";
            }
            if (preg_match('/w:h="(\d+)"/', $pgSz[1], $h)) {
                $heightTwips = intval($h[1]);
                $heightCm = $heightTwips / 567;
                echo "Высота: {$heightTwips} twips = " . round($heightCm, 2) . " см\n";
            }
            if (preg_match('/w:orient="(\w+)"/', $pgSz[1], $orient)) {
                echo "Ориентация: " . htmlspecialchars($orient[1]) . "\n";
            }
            echo "\n";
        }
        
        // Извлекаем поля
        if (preg_match('/<w:pgMar\s+([^>]*)>/', $matches[0], $pgMar)) {
            echo "--- ПОЛЯ СТРАНИЦЫ (w:pgMar) ---\n";
            echo htmlspecialchars($pgMar[0]) . "\n";
            
            $margins = ['top', 'right', 'bottom', 'left'];
            foreach ($margins as $margin) {
                if (preg_match("/w:{$margin}=\"(\d+)\"/", $pgMar[1], $m)) {
                    $twips = intval($m[1]);
                    $cm = $twips / 567;
                    echo ucfirst($margin) . ": {$twips} twips = " . round($cm, 2) . " см\n";
                }
            }
            echo "\n";
        }
    }
    
    // Ищем размеры страницы (альтернативный поиск)
    if (preg_match_all('/<w:pgSz\s+[^>]*\/?>/', $documentXml, $matches)) {
        echo "=== ВСЕ РАЗМЕРЫ СТРАНИЦЫ В ДОКУМЕНТЕ ===\n";
        foreach ($matches[0] as $match) {
            echo htmlspecialchars($match) . "\n";
        }
        echo "\n";
    }
    
    echo "</pre></body></html>";
    
    // Дополнительная информация о таблицах
    if (preg_match_all('/<w:tbl[^>]*>.*?<\/w:tbl>/s', $documentXml, $tableMatches)) {
        echo "=== НАЙДЕНО ТАБЛИЦ: " . count($tableMatches[0]) . " ===\n";
        
        // Анализируем первую таблицу
        if (!empty($tableMatches[0][0])) {
            $firstTable = $tableMatches[0][0];
            
            // Ширина таблицы
            if (preg_match('/<w:tblW[^>]*>/', $firstTable, $tblW)) {
                echo "--- Ширина таблицы ---\n";
                echo htmlspecialchars($tblW[0]) . "\n";
            }
            
            // Стиль таблицы
            if (preg_match('/w:tblStyle="([^"]+)"/', $firstTable, $style)) {
                echo "Стиль таблицы: " . htmlspecialchars($style[1]) . "\n";
            }
            
            // Количество столбцов
            if (preg_match_all('/<w:gridCol[^>]*>/', $firstTable, $cols)) {
                echo "Количество столбцов: " . count($cols[0]) . "\n";
                echo "--- Ширины столбцов ---\n";
                foreach ($cols[0] as $idx => $col) {
                    if (preg_match('/w:w="(\d+)"/', $col, $w)) {
                        $widthTwips = intval($w[1]);
                        $widthCm = $widthTwips / 567;
                        echo "Столбец " . ($idx + 1) . ": {$widthTwips} twips = " . round($widthCm, 2) . " см\n";
                    }
                }
            }
            
            // Параметры таблицы
            if (preg_match('/<w:tblPr>.*?<\/w:tblPr>/s', $firstTable, $tblPr)) {
                echo "--- Параметры таблицы (tblPr) ---\n";
                echo htmlspecialchars(substr($tblPr[0], 0, 500)) . "\n";
            }
            
            // Границы таблицы
            if (preg_match('/<w:tblBorders>.*?<\/w:tblBorders>/s', $firstTable, $borders)) {
                echo "--- Границы таблицы ---\n";
                echo htmlspecialchars($borders[0]) . "\n";
            }
        }
        echo "\n";
    }
    
    // Стили документа
    if ($stylesXml) {
        echo "=== СТИЛИ ДОКУМЕНТА (первые 2000 символов) ===\n";
        echo htmlspecialchars(substr($stylesXml, 0, 2000)) . "\n\n";
    }
    
    // Ищем информацию о шрифтах
    if (preg_match_all('/<w:rFonts[^>]*>/', $documentXml, $fonts)) {
        echo "=== ШРИФТЫ ===\n";
        $uniqueFonts = [];
        foreach ($fonts[0] as $font) {
            if (preg_match('/w:ascii="([^"]+)"/', $font, $m)) {
                $uniqueFonts[$m[1]] = true;
            }
        }
        foreach (array_keys($uniqueFonts) as $font) {
            echo htmlspecialchars($font) . "\n";
        }
        echo "\n";
    }
    
    // Размеры шрифтов
    if (preg_match_all('/<w:sz[^>]*>/', $documentXml, $sizes)) {
        echo "=== РАЗМЕРЫ ШРИФТОВ ===\n";
        $uniqueSizes = [];
        foreach ($sizes[0] as $size) {
            if (preg_match('/w:val="(\d+)"/', $size, $m)) {
                $halfPoints = intval($m[1]);
                $points = $halfPoints / 2;
                $uniqueSizes[$points] = true;
            }
        }
        foreach (array_keys($uniqueSizes) as $size) {
            echo $size . " pt\n";
        }
        echo "\n";
    }
    
    // Выравнивание
    if (preg_match_all('/<w:jc[^>]*>/', $documentXml, $aligns)) {
        echo "=== ВЫРАВНИВАНИЕ ===\n";
        foreach ($aligns[0] as $align) {
            if (preg_match('/w:val="([^"]+)"/', $align, $m)) {
                echo htmlspecialchars($m[1]) . "\n";
            }
        }
        echo "\n";
    }
    
    // Отступы (indent)
    if (preg_match_all('/<w:ind[^>]*>/', $documentXml, $indents)) {
        echo "=== ОТСТУПЫ ===\n";
        foreach ($indents[0] as $indent) {
            echo htmlspecialchars($indent) . "\n";
        }
        echo "\n";
    }
    
    // Междустрочные интервалы
    if (preg_match_all('/<w:spacing[^>]*>/', $documentXml, $spacings)) {
        echo "=== МЕЖДУСТРОЧНЫЕ ИНТЕРВАЛЫ ===\n";
        foreach ($spacings[0] as $spacing) {
            echo htmlspecialchars($spacing) . "\n";
        }
        echo "\n";
    }
    
    // Информация о документе из core.xml и app.xml
    $coreXml = $zip->getFromName('docProps/core.xml');
    if ($coreXml) {
        echo "=== МЕТАДАННЫЕ ДОКУМЕНТА (core.xml) ===\n";
        echo htmlspecialchars(substr($coreXml, 0, 500)) . "\n\n";
    }
    
    $appXml = $zip->getFromName('docProps/app.xml');
    if ($appXml) {
        echo "=== СВОЙСТВА ПРИЛОЖЕНИЯ (app.xml) ===\n";
        if (preg_match('/<Pages>(\d+)<\/Pages>/', $appXml, $pages)) {
            echo "Количество страниц: " . htmlspecialchars($pages[1]) . "\n";
        }
        if (preg_match('/<Words>(\d+)<\/Words>/', $appXml, $words)) {
            echo "Количество слов: " . htmlspecialchars($words[1]) . "\n";
        }
        echo "\n";
    }
    
    $zip->close();
} else {
    echo "Ошибка открытия архива\n";
}

