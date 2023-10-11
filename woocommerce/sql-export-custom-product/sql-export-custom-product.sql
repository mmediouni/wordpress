SELECT 
  p.ID,
  p.post_title,
  t.name AS product_category,
  t.term_id AS product_id,
  t.slug AS product_slug,
  tt.term_taxonomy_id AS tt_term_taxonomy,
  tr.term_taxonomy_id AS tr_term_taxonomy,
  MAX(CASE WHEN pm1.meta_key = '_stock' then pm1.meta_value ELSE NULL END) as stock,
  MAX(CASE WHEN pm1.meta_key = '_price' then pm1.meta_value ELSE NULL END) as price,
  MAX(CASE WHEN pm1.meta_key = '_regular_price' then pm1.meta_value ELSE NULL END) as regular_price,
  MAX(CASE WHEN pm1.meta_key = '_sale_price' then pm1.meta_value ELSE NULL END) as sale_price,
  MAX(CASE WHEN pm1.meta_key = '_sku' then pm1.meta_value ELSE NULL END) as sku,
  GROUP_CONCAT(CONCAT(pm.meta_key, ': ', pm.meta_value) SEPARATOR '; ') AS metafields
FROM wp_posts p 
LEFT JOIN wp_postmeta pm1 ON pm1.post_id = p.ID
LEFT JOIN wp_term_relationships AS tr ON tr.object_id = p.ID
JOIN wp_term_taxonomy AS tt ON tt.taxonomy = 'product_cat' AND tt.term_taxonomy_id = tr.term_taxonomy_id 
JOIN wp_terms AS t ON t.term_id = tt.term_id
LEFT JOIN wp_postmeta pm ON pm.post_id = p.ID
WHERE p.post_type in('product', 'product_variation') AND p.post_status = 'publish' AND p.post_content <> ''
GROUP BY p.ID, p.post_title
ORDER BY p.ID ASC;
