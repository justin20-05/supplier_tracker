<?php
// includes/pagination.php

/**
 * Renders a Tailwind-styled pagination bar
 * @param int $page Current page
 * @param int $totalPages Total number of pages
 * @param int $totalRows Total number of records
 * @param int $limit Records per page
 */
function renderPagination($page, $totalPages, $totalRows, $limit) {
    if ($totalPages <= 1) return;

    $offset = ($page - 1) * $limit;
    $urlParams = $_GET; // Keeps search/filter queries active
    ?>
    <div class="flex items-center justify-between mt-6 px-2">
        <p class="text-xs text-gray-500 font-medium">
            Showing <span class="text-gray-900 font-bold"><?= $offset + 1 ?></span> to 
            <span class="text-gray-900 font-bold"><?= min($offset + $limit, $totalRows) ?></span> of 
            <span class="text-gray-900 font-bold"><?= $totalRows ?></span> entries
        </p>
        
        <div class="flex gap-1">
            <?php if ($page > 1): ?>
                <?php $urlParams['page'] = $page - 1; ?>
                <a href="?<?= http_build_query($urlParams) ?>" class="p-2 border rounded-xl hover:bg-gray-50 text-gray-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <?php $urlParams['page'] = $i; ?>
                <a href="?<?= http_build_query($urlParams) ?>" 
                   class="px-4 py-2 border rounded-xl text-sm font-bold transition <?= $i == $page ? 'bg-blue-600 text-white border-blue-600 shadow-md shadow-blue-100' : 'bg-white text-gray-600 hover:bg-gray-50' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
                <?php $urlParams['page'] = $page + 1; ?>
                <a href="?<?= http_build_query($urlParams) ?>" class="p-2 border rounded-xl hover:bg-gray-50 text-gray-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            <?php endif; ?>
        </div>
    </div>
    <?php
}
?>