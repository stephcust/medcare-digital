<?php

namespace App\Helpers;

class LoginFunctions
{
    private static function routeToURL(string $routeName)
    {
        $url = '';
        $routeName ??= '';
        if ($routeName) {
            try {
                $url = route($routeName);
            } catch (\Exception $e) {
                $url = '#not-found';
            }
        }
        return [$routeName, $url];
    }
    public static function filterCollectRoutes(array $arr)
    {
        $routes = [];
        foreach ($arr as $item) {
            list($routeName, $url) = self::routeToURL($item['url'] ?? '');
            $routes[$routeName] = $url;
        }
        return collect($routes);
    }

    public static function montarMenusPorPermissoes(array $permissoes)
    {
        $rootMenus = array_filter($permissoes, fn($perm) => $perm['item_id'] === null);
        $left_sidebar = [];
        $topnav = [];
        $topnav_user = [];
        $topnav_right = [];

        foreach ($rootMenus as $rootMenu) {
            $menuConfig = self::configurarMenu($rootMenu, $permissoes);
            //* Se $menuConfig for null, não deve ser exibido
            if (!$menuConfig) {
                continue;
            }

            // * Adicionar ao menu correto
            $lista = $rootMenu['type_menu'];
            $$lista[] = $menuConfig;
        }
        $menus = compact(['left_sidebar', 'topnav', 'topnav_user', 'topnav_right']);
        return $menus;
    }

    private static function montarSubMenus($menu, array &$permissoes)
    {
        $subPermissoes = array_filter($permissoes, fn($perm) => $perm['item_id'] === $menu['id']);
        $subMenus = [];
        foreach ($subPermissoes as $menu) {
            $menuConfig = self::configurarMenu($menu, $permissoes);
            //* Se $menuConfig for null, não deve ser exibido
            if (!$menuConfig) {
                continue;
            }
            $subMenus[] = $menuConfig;
        }
        return $subMenus;
    }

    private static function configurarMenu($menu, array &$permissoes)
    {
        //* Verificar se o menu deve ser exibido
        if ($menu['show_menu'] == 0 || $menu['status'] == 0) {
            return null;
        }
        //* Configuração para PrimeVue
        // Label
        $menuConfig = ["label" => $menu['name']];
        // Icone
        if ($menu['icon'] ?? null) {
            $menuConfig['icon'] = $menu['icon'];
        }
        // Menu Clicável ou Expansível
        if ($menu['is_menu'] == 1) {
            //* Expansível
            $subMenus = self::montarSubMenus($menu, $permissoes);
            $menuConfig['items'] = $subMenus;
        } else {
            //* Clicável (Link href com/sem target)
            list($routeName, $url) = self::routeToURL($menu['url'] ?? '');
            $menuConfig['route'] = $routeName;
            $menuConfig['href'] = $url;
            if ($menu['target'] ?? null) {
                $menuConfig['target'] = $menu['target'];
            }
        }
        return $menuConfig;
    }
}
