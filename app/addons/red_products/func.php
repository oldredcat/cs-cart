<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_red_get_products_cron_job()
{
    //  получаем массив количества товаров по категориям
    fn_red_get_product_counts_by_categories(false);
}

function fn_red_get_products_set_count_products($category_id, $count)
{
    db_query('UPDATE ?:categories SET product_count = ?i WHERE category_id = ?i', $count, $category_id);
}

/**
 * Get the count of products of specified category
 *
 * @param   int     Specified category ID
 * @return  int     Count of products
 */
function fn_red_get_products_count_by_category($category_id)
{
    //  выбираем только активные товары (которые - вкл, status = A)
    return (int) db_get_field(
        'SELECT COUNT(*) FROM ?:products_categories ' .
        'JOIN ?:products ON ?:products_categories.product_id = ?:products.product_id ' .
        'WHERE ?:products_categories.category_id = ?i AND ?:products.status = ?s', $category_id, 'A'
    );
}

/**
 * Get the array of counts of products [category_id => count]
 *
 * @return  array
 */
function fn_red_get_product_counts_by_categories($cached = true)
{
    if ($cached) {
        //  пытаемся взять значения из кэша
        $categories = \Tygh\Registry::get('red_products_count_by_category');
    } else {
        $categories = null;
    }

    //  если не получилось взять из кэша - пересчитываем и сохраняем в кэш
    if (is_null($categories)) {

        $categories = [];

        //  все категории какие есть
        $categories_list = fn_get_plain_categories_tree();

        $temp = [];
        foreach ($categories_list as $category) {
            $temp[$category['category_id']] = $category;
        }
        $categories_list = $temp;

        //  для всех категорий находим изначальное количество товаров в них
        foreach ($categories_list as $value) {
            $value['products_count'] = fn_red_get_products_count_by_category($value['category_id']);
            $categories[$value['category_id']] = $value;
        }

        foreach ($categories as $category) {

            $path = explode('/', $category['id_path']);

            //  удаляем последнюю категорию, т.к. для неё уже посчитаны товары
            $current = array_pop($path);

            //  обновляем значение в базе для текущей категории
            fn_red_get_products_set_count_products($current, $category['products_count']);

            while (count($path)) {
                $category_id = array_pop($path);
                $categories[$category_id]['products_count'] += $category['products_count'];
                //  обновляем значение в базе для остальных категорий
                fn_red_get_products_set_count_products($category_id, $categories[$category_id]['products_count']);
            }
        }

        if ($cached) {
            \Tygh\Registry::set('red_products_count_by_category', $categories);
        }
    }

    //  возвращаем массив
    return $categories;
}

function fn_red_products_render_block_pre(&$block, &$block_schema, &$params, &$block_content)
{
    //  проверяем наш блок или нет?
    if (isset($block['content']['items']) && $block['content']['items']['filling'] == 'category') {

        //  получаем список ИД категорий для обработки
        $category_ids = explode(',', $block['content']['items']['cid']);

        //  получаем массив количества товаров по категориям
        $categories = fn_red_get_product_counts_by_categories();

        //  подсчитываем количество товаров всего в нужных категориях
        $products_count = 0;
        foreach ($categories as $category) {
            if (in_array($category['category_id'], $category_ids)) {
                $products_count += $category['products_count'];
            }
        }

        //  Дополняем заголовок блока, типа "Планшеты (6)", "Планшеты и мониторы (10)"
        $block['name'] .= ' (' . $products_count . ')';
    }
}