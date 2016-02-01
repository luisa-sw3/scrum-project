<?php

namespace Util;

/**
 * Paginator class
 * @author Cesar Giraldo  <cesargiraldo1108@gmail.com> 31/01/2016
 */
class Paginator {

    const ORDER_ASC = 'asc';
    const ORDER_DESC = 'desc';
    const REQUEST_TYPE_ARRAY = 'array';
    const REQUEST_TYPE_REQUEST = 'request';

    /**
     * Rangos de paginacion por defecto
     * @var array[integer] arreglo de numeros para controlar la paginacion por defecto 
     */
    public static $defaultPageRanges = array(10, 25, 50, 100);

    /**
     * Numero por defector de items por pagina para el paginador
     * @var integer  
     */
    public static $defaultItemsPerPage = 10;

    /**
     * Numero por defector de items por pagina para el paginador
     * @var integer  
     */
    public static $defaultMaxPagerItems = 8;

    /**
     * Esta funcion permite instanciar un nuevo paginador para los listados
     * de la aplicacion, seteando los valores por defecto o parametrizados
     * por el usuario
     * @author Cesar Giraldo  <cesargiraldo1108@gmail.com> 27/08/2015
     * @param \Symfony\Bundle\FrameworkBundle\Controller\Controller $controller
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Ideup\SimplePaginatorBundle\Paginator\Paginator instancia del paginador
     */
    public static function createPaginator($controller, $request) {
        $paginator = $controller->get('ideup.simple_paginator');
        $paginator->pageRanges = Paginator::$defaultPageRanges;
        $paginator->setItemsPerPage($request->get('itemsPerPage') != '' ? $request->get('itemsPerPage') : Paginator::$defaultItemsPerPage);
        if ($paginator->getItemsPerPage() == 0) {
            $paginator->setItemsPerPage(Paginator::$defaultPageRanges[count(Paginator::$defaultPageRanges) - 1]);
        }
        $paginator->setMaxPagerItems(Paginator::$defaultMaxPagerItems);
        return $paginator;
    }

    /**
     * Esta funcion permite instanciar un nuevo paginador para los listados
     * de la aplicacion, seteando los valores por defecto o parametrizados
     * por el usuario
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 27/08/2015
     * @param \Symfony\Bundle\FrameworkBundle\Controller\Controller $controller
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Ideup\SimplePaginatorBundle\Paginator\Paginator instancia del paginador
     */
    public static function createPaginators($controller, $request, array $ids) {
        $paginator = $controller->get('ideup.simple_paginator');
        $paginator->pageRanges = Paginator::$defaultPageRanges;
        foreach ($ids as $id) {            
            $paginator->setItemsPerPage($request->get('itemsPerPage') != '' ? $request->get('itemsPerPage') : Paginator::$defaultItemsPerPage , $id);
            if ($paginator->getItemsPerPage($id) == 0) {
                $paginator->setItemsPerPage(Paginator::$defaultPageRanges[count(Paginator::$defaultPageRanges) - 1], $id);
            }
            $paginator->setMaxPagerItems(Paginator::$defaultMaxPagerItems,$id);
        }

        return $paginator;
    }

    /**
     * Esta funcion permite filtrar de un arreglo los parametros de busqueda u ordenamiento
     * con los indices indicados en otro arreglo u objeto request, metodo utilizado para los paginadores
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 27/08/2015
     * @param array[string] $index arreglo con las claves que contiene el arreglo de parametros
     * @param \Symfony\Component\HttpFoundation\Request $request peticion $_POST, array[string] parametros sin filtrar
     * @return array[string] arreglo filtrado
     */
    public static function filterParameters($index, $parametersRequest, $type, $castToInteger = null) {

        $arrayFiltered = array();
        foreach ($index as $item) {
            if ($type == self::REQUEST_TYPE_ARRAY) {
                if (isset($parametersRequest[$item]) && $parametersRequest[$item] != '') {
                    if (!is_array($parametersRequest[$item])) {
                        $filterItem = preg_replace('@[ ]{2,}@', ' ', trim($parametersRequest[$item]));
                    }
                }
                if ($castToInteger == true) {
                    $filterItem = (int) $filterItem;
                }
                if (isset($filterItem) && $filterItem != '') {
                    $arrayFiltered[$item] = $filterItem;
                    $filterItem = '';
                }
            } elseif ($type == self::REQUEST_TYPE_REQUEST) {
                $filterItem = preg_replace('@[ ]{2,}@', ' ', trim($parametersRequest->query->get($item)));

                if ($castToInteger == true) {
                    $filterItem = (int) $filterItem;
                }
                if (isset($filterItem) && $filterItem != '') {
                    $arrayFiltered[$item] = $filterItem;
                    $filterItem = '';
                }
            }
        }
        return $arrayFiltered;
    }

    /**
     * Esta funcion permite construir una url, correspondiente a una peticion $_GET a partir
     * de un arreglo de parametros cualquiera.
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 27/08/2015
     * @param array[string] $index indices del arreglo de parametros
     * @param array[string] $search arreglo de parametros
     * @return string cadena de texto con la url correspondiente
     */
    public static function getUrlFromParameters($index, $search) {
        $url = '';
        foreach ($index as $item) {
            if (isset($search[$item]) && $search[$item] != '') {
                if (!is_array($search[$item])) {
                    $url = $url . '&' . $item . "=" . $search[$item];
                }
            }
        }
        return $url;
    }

    /**
     * Esta funcion prmite construir un arreglo el cual contiene los datos para realizar
     * las tareas de ordenamiento en un listado cualquiera
     * @author Cesar Naranjo <cesargiraldo1108@gmail.com> 27/08/2015
     * @param array[string] $index indices del arreglo de parametros
     * @param array[string] $order arreglo de parametros
     * @return array[string url, array[order, image]] elementos para el ordemaniento
     */
    public static function getUrlOrderFromParameters($index, $order) {

        $orderBy = array();
        $orderBy['url'] = null;
        foreach ($index as $item) {
            $orderBy[$item] = array();
            $orderBy[$item]['order'] = null;
            $orderBy[$item]['orderType'] = null;

            if (isset($order[$item]) && $order[$item] != '') {
                $orderBy[$item]['order'] = $order[$item] + 1;
                $orderBy['url'] = $orderBy['url'] . '&' . $item . "=" . $order[$item];

                if ($orderBy[$item]['order'] % 2) {
                    $orderBy[$item]['orderType'] = self::ORDER_ASC;
                } else {
                    $orderBy[$item]['orderType'] = self::ORDER_DESC;
                }
            } else {
                $orderBy[$item]['order'] = 1;
            }
        }
        return $orderBy;
    }

    /**
     * Esta funcion permite setear en session los arreglos de busqueda y ordenamiento
     * de listados definidos en la aplicacion.
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 28/08/2015
     * @param Request $request solicitud del cliente
     * @param string $searchName identificador del listado 
     * @param array[string] $search parametros de busqueda
     * @param array[string] $order parametros de ordenamiento
     */
    public static function setLastSearchOnSession($request, $searchName, $search, $order) {

        $page = (int) $request->get('page');
        $itemsPerPage = (int) $request->get('itemsPerPage');

        if ($page > 0 || $itemsPerPage) {
            $search['page'] = $page;
            $search['itemsPerPage'] = $itemsPerPage;
        }

        $session = $request->getSession();
        if (!empty($search) || !empty($order)) {
            $lastSearch = array($search, $order);
            $session->set($searchName, $lastSearch);
        }
    }

    /**
     * Esta funcion permite obtener un arreglo de busqueda y ordenamiento almacenado
     * en session y retornarlo como otro arreglo plano
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 28/09/2015
     * @param Request $request solicitud del usuario
     * @param string $searchName clave del arreglo en session
     * @return array[string] arreglo reestructurado
     */
    public static function getLastSearchOnSession($request, $searchName) {

        $session = $request->getSession();
        $fullSearch = array();
        $lastSearch = $session->get($searchName);

        if (!empty($lastSearch)) {
            if (isset($lastSearch[0]) && isset($lastSearch[1])) {
                $fullSearch = array_merge($lastSearch[0], $lastSearch[1]);
            }
        }
        return $fullSearch;
    }

    /**
     * Esta funcion permite construir el arreglo de datos que necesita el twig para 
     * pintar los datos de busqueda y ordenamiento en pantalla
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 28/08/2015
     * @param array[string] $indexSearch
     * @param array[string] $search
     * @param array[string] $indexOrder
     * @param array[string] $order
     * @param array[string] $paginator
     * @return array[string]
     */
    public static function buildSearcherData($indexSearch, $search, $indexOrder, $order, $paginator, $id = null) {

        $params = Paginator::getUrlFromParameters($indexSearch, $search);
        $orderBy = Paginator::getUrlOrderFromParameters($indexOrder, $order);
        $searcherData = array(
            'search' => $search,
            'order' => $order,
            'params' => $params,
            'orderBy' => $orderBy,
            'itemsPageUrl' => '&itemsPerPage=' . $paginator->getItemsPerPage($id),
        );

        return $searcherData;
    }

}
