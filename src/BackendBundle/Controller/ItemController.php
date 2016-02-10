<?php

namespace BackendBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use BackendBundle\Form\ItemType;
use BackendBundle\Form\SearchItemType;
use BackendBundle\Entity as Entity;
use Util\Paginator;

/**
 * Item controller.
 */
class ItemController extends Controller {

    const MENU = 'menu_project_backlog';

    /**
     * Permite listar el backlog de un proyecto
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 25/01/2016
     * @param Request $request
     * @param string $id identificador del proyecto
     * @return type
     */
    public function productBacklogAction(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();

        $project = $em->getRepository('BackendBundle:Project')->find($id);

        if (!$project) {
            $this->get('session')->getFlashBag()->add('messageError', $this->get('translator')->trans('backend.project.not_found_message'));
            return $this->redirectToRoute('backend_projects');
        }

        $item = new Entity\Item();
        $item->setProject($project);
        $searchForm = $this->createForm(SearchItemType::class, $item);

        $indexSearch = array('item_designed_user', 'item_free_text', 'item_type');

        if ($request->isMethod('POST')) {
            $searchForm->handleRequest($request);
            $parameters = $request->request->get('backendbundle_search_item_type');
            $search = Paginator::filterParameters($indexSearch, $parameters, Paginator::REQUEST_TYPE_ARRAY);
            $search['id'] = $id;
            return $this->redirect($this->generateUrl('backend_project_product_backlog', $search));
        } else {
            $search = Paginator::filterParameters($indexSearch, $request, Paginator::REQUEST_TYPE_REQUEST);
            $search['project'] = $project->getId();
            $search['sprint'] = NULL;
        }

        $query = $em->getRepository('BackendBundle:Item')->findItems($search);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
                $query, $request->query->getInt('page', 1), Paginator::DEFAULT_ITEMS_PER_PAGE);

        return $this->render('BackendBundle:Project/ProductBacklog:index.html.twig', array(
                    'project' => $project,
                    'menu' => self::MENU,
                    'pagination' => $pagination,
                    'searchForm' => $searchForm->createView(),
        ));
    }

    /**
     * Permite crear un item en el sistema
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 21/01/2016
     * @param Request $request
     * @param string $id identificador del proyecto
     * @return type
     */
    public function newAction(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();

        $project = $em->getRepository('BackendBundle:Project')->find($id);

        if (!$project) {
            $this->get('session')->getFlashBag()->add('messageError', $this->get('translator')->trans('backend.project.not_found_message'));
            return $this->redirectToRoute('backend_projects');
        }

        $item = new Entity\Item();
        $item->setProject($project);

        $form = $this->createForm(ItemType::class, $item);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $item->setProject($project);
            $item->setUserOwner($this->getUser());

            $em->persist($item);
            $em->flush();

            $this->get('session')->getFlashBag()->add('messageSuccess', $this->get('translator')->trans('backend.item.creation_success_message'));
            return $this->redirectToRoute('backend_project_product_backlog', array('id' => $project->getId()));
        }

        return $this->render('BackendBundle:Project/ProductBacklog:new.html.twig', array(
                    'project' => $project,
                    'form' => $form->createView(),
                    'menu' => self::MENU
        ));
    }

    /**
     * Permite editar la informacion de un item
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 27/01/2016
     * @param Request $request
     * @param string $id identificador del proyecto
     * @param string $itemId identificador del itemm
     * @return type
     */
    public function editAction(Request $request, $id, $itemId) {
        $em = $this->getDoctrine()->getManager();
        $item = $em->getRepository('BackendBundle:Item')->find($itemId);

        if (!$item || ($item && $item->getProject()->getId() != $id)) {
            $this->get('session')->getFlashBag()->add('messageError', $this->get('translator')->trans('backend.item.not_found_message'));
            return $this->redirectToRoute('backend_project_product_backlog', array('id' => $id));
        }

        $editForm = $this->createForm(ItemType::class, $item);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            
            $em->persist($item);
            $em->flush();

            $this->get('session')->getFlashBag()->add('messageSuccess', $this->get('translator')->trans('backend.item.update_success_message'));
            return $this->redirectToRoute('backend_project_product_backlog', array('id' => $item->getProject()->getId()));
        }

        return $this->render('BackendBundle:Project/ProductBacklog:edit.html.twig', array(
                    'item' => $item,
                    'project' => $item->getProject(),
                    'edit_form' => $editForm->createView(),
                    'menu' => self::MENU
        ));
    }

    /**
     * Esta funcion permite realizar la carga de archivos anexos a un item
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 02/09/2016
     * @param Request $request datos de la solicitud
     * @param string $id identificador del proyecto
     * @param string $itemId identificador del item
     * @return JsonResponse JSON con mensaje de respuesta
     */
    public function uploadAttachmentAction(Request $request, $id, $itemId) {

        $response = array('result' => '__OK__', 'msg' => '');

        $em = $this->getDoctrine()->getManager();
        $item = $em->getRepository('BackendBundle:Item')->find($itemId);

        if (!$item || ($item && $item->getProject()->getId() != $id)) {
            $response['result'] = '__KO__';
            $response['msg'] = $this->get('translator')->trans('backend.item.not_found_message');
            return new JsonResponse($response);
        }

        $extensions = array('avi', 'm4a', 'mp4',
            'bmpr', 'ttf', 'woff2',
            'jpg', 'png', 'JPG', 'bmp',
            'docx', 'doc', 'pages', 'pdf', 'xml',
            'zip', 'csv', 'json', 'txt', 'xls', 'xlsx');

        $maxSize = 10250000; //10 MB

        if (isset($_FILES['uploaded_file'])) {

            $fileInfo = new \SplFileInfo($_FILES['uploaded_file']['name']);
            $fileExtension = $fileInfo->getExtension();

            if (in_array($fileExtension, $extensions)) {

                $fileSize = $_FILES['uploaded_file']['size'];
                if ($fileSize <= $maxSize) {

                    $fileName = uniqid('attach-') . $_FILES['uploaded_file']['name'];

                    $attachment = new Entity\ItemAttachment();
                    $attachment->setFileExtension($fileExtension);
                    $attachment->setFilePath($fileName);
                    $attachment->setItem($item);
                    $attachment->setName($_FILES['uploaded_file']['name']);
                    $attachment->setUserOwner($this->getUser());

                    $directory = $this->container->getParameter('item_attachments_folder');
                    if (!file_exists($directory)) {
                        mkdir($directory);
                    }

                    try {
                        if (move_uploaded_file($_FILES['uploaded_file']['tmp_name'], $directory . $fileName)) {
                            $em->persist($attachment);
                            $em->flush();
                        } else {
                            $response['result'] = '__KO__';
                            $response['msg'] = $this->get('translator')->trans('backend.attachment.error_uploading');
                        }
                    } catch (\Exception $exc) {
                        //echo $exc->getTraceAsString();
                        $response['result'] = '__KO__';
                        $response['msg'] = $this->get('translator')->trans('backend.attachment.error_uploading');
                    }
                } else {
                    $response['result'] = '__KO__';
                    $response['msg'] = $this->get('translator')->trans('backend.attachment.size_exceeded');
                }
            } else {
                $response['result'] = '__KO__';
                $response['msg'] = $this->get('translator')->trans('backend.attachment.invalid_extension');
            }
        } else {
            $response['result'] = '__KO__';
            $response['msg'] = $this->get('translator')->trans('backend.attachment.not_found');
        }
        return new JsonResponse($response);
    }

}
