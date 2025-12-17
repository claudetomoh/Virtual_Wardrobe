<?php
/**
 * Update Database Table References
 * This script updates all SQL queries to use the new table prefix
 * Run this once after updating config.php and db_init.sql
 */

$baseDir = __DIR__;
$phpFiles = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($baseDir . '/src'),
    RecursiveIteratorIterator::SELF_FIRST
);

$replacements = [
    ' users ' => ' ' . 'vw_users' . ' ',
    ' clothes ' => ' ' . 'vw_clothes' . ' ',
    ' outfits ' => ' ' . 'vw_outfits' . ' ',
    ' outfits_planned ' => ' ' . 'vw_outfits_planned' . ' ',
    ' shared_outfits ' => ' ' . 'vw_shared_outfits' . ' ',
    ' password_resets ' => ' ' . 'vw_password_resets' . ' ',
    ' audit_log ' => ' ' . 'vw_audit_log' . ' ',
    ' planner_updates ' => ' ' . 'vw_planner_updates' . ' ',
    ' collections ' => ' ' . 'vw_collections' . ' ',
    ' collection_items ' => ' ' . 'vw_collection_items' . ' ',
    ' login_attempts ' => ' ' . 'vw_login_attempts' . ' ',
    
    'FROM users' => 'FROM vw_users',
    'FROM clothes' => 'FROM vw_clothes',
    'FROM outfits' => 'FROM vw_outfits',
    'FROM outfits_planned' => 'FROM vw_outfits_planned',
    'FROM shared_outfits' => 'FROM vw_shared_outfits',
    'FROM password_resets' => 'FROM vw_password_resets',
    'FROM audit_log' => 'FROM vw_audit_log',
    'FROM planner_updates' => 'FROM vw_planner_updates',
    'FROM collections' => 'FROM vw_collections',
    'FROM collection_items' => 'FROM vw_collection_items',
    'FROM login_attempts' => 'FROM vw_login_attempts',
    
    'INTO users' => 'INTO vw_users',
    'INTO clothes' => 'INTO vw_clothes',
    'INTO outfits' => 'INTO vw_outfits',
    'INTO outfits_planned' => 'INTO vw_outfits_planned',
    'INTO shared_outfits' => 'INTO vw_shared_outfits',
    'INTO password_resets' => 'INTO vw_password_resets',
    'INTO audit_log' => 'INTO vw_audit_log',
    'INTO planner_updates' => 'INTO vw_planner_updates',
    'INTO collections' => 'INTO vw_collections',
    'INTO collection_items' => 'INTO vw_collection_items',
    'INTO login_attempts' => 'INTO vw_login_attempts',
    
    'UPDATE users' => 'UPDATE vw_users',
    'UPDATE clothes' => 'UPDATE vw_clothes',
    'UPDATE outfits' => 'UPDATE vw_outfits',
    'UPDATE outfits_planned' => 'UPDATE vw_outfits_planned',
    'UPDATE shared_outfits' => 'UPDATE vw_shared_outfits',
    'UPDATE password_resets' => 'UPDATE vw_password_resets',
    'UPDATE audit_log' => 'UPDATE vw_audit_log',
    'UPDATE planner_updates' => 'UPDATE vw_planner_updates',
    'UPDATE collections' => 'UPDATE vw_collections',
    'UPDATE collection_items' => 'UPDATE vw_collection_items',
    'UPDATE login_attempts' => 'UPDATE vw_login_attempts',
    
    'JOIN users' => 'JOIN vw_users',
    'JOIN clothes' => 'JOIN vw_clothes',
    'JOIN outfits' => 'JOIN vw_outfits',
    'JOIN outfits_planned' => 'JOIN vw_outfits_planned',
    'JOIN shared_outfits' => 'JOIN vw_shared_outfits',
    'JOIN password_resets' => 'JOIN vw_password_resets',
    'JOIN audit_log' => 'JOIN vw_audit_log',
    'JOIN planner_updates' => 'JOIN vw_planner_updates',
    'JOIN collections' => 'JOIN vw_collections',
    'JOIN collection_items' => 'JOIN vw_collection_items',
    'JOIN login_attempts' => 'JOIN vw_login_attempts',
    
    'DELETE FROM users' => 'DELETE FROM vw_users',
    'DELETE FROM clothes' => 'DELETE FROM vw_clothes',
    'DELETE FROM outfits' => 'DELETE FROM vw_outfits',
    'DELETE FROM outfits_planned' => 'DELETE FROM vw_outfits_planned',
    'DELETE FROM shared_outfits' => 'DELETE FROM vw_shared_outfits',
    'DELETE FROM password_resets' => 'DELETE FROM vw_password_resets',
    'DELETE FROM audit_log' => 'DELETE FROM vw_audit_log',
    'DELETE FROM planner_updates' => 'DELETE FROM vw_planner_updates',
    'DELETE FROM collections' => 'DELETE FROM vw_collections',
    'DELETE FROM collection_items' => 'DELETE FROM vw_collection_items',
    'DELETE FROM login_attempts' => 'DELETE FROM vw_login_attempts',
];

$filesUpdated = 0;
$totalReplacements = 0;

foreach ($phpFiles as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $filepath = $file->getPathname();
        $content = file_get_contents($filepath);
        $originalContent = $content;
        
        foreach ($replacements as $search => $replace) {
            $count = 0;
            $content = str_replace($search, $replace, $content, $count);
            $totalReplacements += $count;
        }
        
        if ($content !== $originalContent) {
            file_put_contents($filepath, $content);
            $filesUpdated++;
            echo "Updated: " . str_replace($baseDir, '', $filepath) . "\n";
        }
    }
}

echo "\n=================================\n";
echo "Conversion Complete!\n";
echo "Files updated: $filesUpdated\n";
echo "Total replacements: $totalReplacements\n";
echo "=================================\n";
echo "\nNOTE: Please review the changes and test thoroughly.\n";
echo "All table names now have 'vw_' prefix to avoid conflicts.\n";
