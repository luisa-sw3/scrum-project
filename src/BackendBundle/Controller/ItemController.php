<?php

namespace BackendBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use BackendBundle\Form\ItemType;
use BackendBundle\Form\ItemSimpleType;
use BackendBundle\Form\SearchItemType;
use BackendBundle\Entity as Entity;
use Util\Paginator;
use BackendBundle\Form\MoveItemToSprintType;
use BackendBundle\Form\MoveItemToProjectType;

/**
 * Item controller.
 */
class ItemController extends Controller {

    const MENU = 'menu_project_backlog';
    const MENU_SPRINTS = 'menu_project_sprints';

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

        if (!$project || ($project && !$this->container->get('access_control')->isAllowedProject($project->getId()))) {
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
            $search['parent'] = NULL;
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

        if (!$project || ($project && !$this->container->get('access_control')->isAllowedProject($project->getId()))) {
            $this->get('session')->getFlashBag()->add('messageError', $this->get('translator')->trans('backend.project.not_found_message'));
            return $this->redirectToRoute('backend_projects');
        }

        $item = new Entity\Item();
        $item->setProject($project);

        $menu = self::MENU;
        if (!empty($request->get('sprintId'))) {
            $sprint = $em->getRepository('BackendBundle:Sprint')->find($request->get('sprintId'));
            $item->setSprint($sprint);
            $menu = self::MENU_SPRINTS;
        }

        $form = $this->createForm(ItemType::class, $item);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $item->setProject($project);
            $item->setUserOwner($this->getUser());

            $em->persist($item);
            $em->flush();

            $this->get('session')->getFlashBag()->add('messageSuccess', $this->get('translator')->trans('backend.item.creation_success_message'));

            //guardamos el registro en el historial
            $this->container->get('app_history')->saveItemHistory($item, Entity\ItemHistory::ITEM_CREATED);

            if (!empty($request->get('sprintId'))) {
                return $this->redirectToRoute('backend_project_sprints_backlog', array('id' => $project->getId(), 'sprintId' => $request->get('sprintId')));
            }
            return $this->redirectToRoute('backend_project_product_backlog', array('id' => $project->getId()));
        }

        return $this->render('BackendBundle:Project/ProductBacklog:new.html.twig', array(
                    'project' => $project,
                    'form' => $form->createView(),
                    'item' => $item,
                    'menu' => $menu,
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
        $previousItem = clone $item;
        if (!$this->container->get('access_control')->isAllowedProject($id)) {
            $this->get('session')->getFlashBag()->add('messageError', $this->get('translator')->trans('backend.project.not_found_message'));
            return $this->redirectToRoute('backend_projects');
        }

        if (!$item || ($item && $item->getProject()->getId() != $id)) {
            $this->get('session')->getFlashBag()->add('messageError', $this->get('translator')->trans('backend.item.not_found_message'));
            return $this->redirectToRoute('backend_project_product_backlog', array('id' => $id));
        }

        $editForm = $this->createForm(ItemType::class, $item);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {

            //verificamos los ciclos de correccion del item
            if ($previousItem->getStatus() != $item->getStatus() && $item->getStatus() == Entity\Item::STATUS_READY_FOR_TESTING) {
                $item->setFixedOnCycle($item->getFixedOnCycle() + 1);
            }
            
            $em->persist($item);
            $em->flush();

            //solicitamos al historial verificar los cambios realizados sobre el item
            $this->container->get('app_history')->findItemChanges($previousItem, $item);

            $this->get('session')->getFlashBag()->add('messageSuccess', $this->get('translator')->trans('backend.item.update_success_message'));

            if (!empty($request->get('sprintId'))) {
                return $this->redirectToRoute('backend_project_sprints_backlog', array('id' => $id, 'sprintId' => $request->get('sprintId')));
            }

            return $this->redirectToRoute('backend_project_product_backlog', array('id' => $item->getProject()->getId()));
        }

        $search = array('item' => $itemId);
        $order = array('uploadDate' => 'DESC');
        $attachments = $em->getRepository('BackendBundle:ItemAttachment')->findBy($search, $order);
        $itemHistory = $em->getRepository('BackendBundle:ItemHistory')->findBy($search, array('date' => 'DESC'));

        $menu = self::MENU;
        if (!empty($request->get('sprintId'))) {
            $menu = self::MENU_SPRINTS;
        }

        return $this->render('BackendBundle:Project/ProductBacklog:edit.html.twig', array(
                    'item' => $item,
                    'attachments' => $attachments,
                    'itemHistory' => $itemHistory,
                    'project' => $item->getProject(),
                    'edit_form' => $editForm->createView(),
                    'menu' => $menu,
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

        $response = array('result' => '__KO__', 'msg' => '');

        $em = $this->getDoctrine()->getManager();
        $item = $em->getRepository('BackendBundle:Item')->find($itemId);

        if (!$item || ($item && $item->getProject()->getId() != $id)) {
            $response['msg'] = $this->get('translator')->trans('backend.item.not_found_message');
            return new JsonResponse($response);
        }

        if (!$this->container->get('access_control')->isAllowedProject($id)) {
            $response['result'] = '__KO__';
            $response['msg'] = $this->get('translator')->trans('backend.project.not_found_message');
            return new JsonResponse($response);
        }


        $attachment = new Entity\ItemAttachment();

        if (isset($_FILES['uploaded_file'])) {

            $fileInfo = new \SplFileInfo($_FILES['uploaded_file']['name']);
            $fileExtension = $fileInfo->getExtension();

            if (in_array($fileExtension, Entity\ItemAttachment::getAvailableExtensions())) {

                $fileSize = $_FILES['uploaded_file']['size'];
                if ($fileSize <= Entity\ItemAttachment::MAX_FILE_SIZE) {

                    $fileName = uniqid('attach-') . $_FILES['uploaded_file']['name'];
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

                            //guardamos el registro en el historial
                            $this->container->get('app_history')->saveItemHistory($item, Entity\ItemHistory::ITEM_ATTACHMENT_ADDED, null, ': <strong>' . $attachment->getName() . '</strong>');

                            $html = $this->renderView('BackendBundle:Project/ProductBacklog:attachmentDetails.html.twig', array(
                                'attach' => $attachment,
                                'project' => $item->getProject(),
                            ));

                            $response['result'] = '__OK__';
                            $response['msg'] = $this->get('translator')->trans('backend.attachment.upload_success');
                            $response['html'] = $html;
                        } else {
                            $response['msg'] = $this->get('translator')->trans('backend.attachment.error_uploading');
                        }
                    } catch (\Exception $exc) {
                        $response['msg'] = $this->get('translator')->trans('backend.attachment.error_uploading');
                    }
                } else {
                    $response['msg'] = $this->get('translator')->trans('backend.attachment.size_exceeded');
                }
            } else {
                $response['msg'] = $this->get('translator')->trans('backend.attachment.invalid_extension') . $attachment->getTextExtensions();
            }
        } else {
            $response['msg'] = $this->get('translator')->trans('backend.attachment.not_found');
        }
        return new JsonResponse($response);
    }

    /**
     * Esta funcion permite realizar la eliminacion de un archivo, 
     * tando del registro en base de datos como de la carpeta donde 
     * se encuentra alojado
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 02/10/2016
     * @param Request $request datos de la solicitud
     * @param string $id identificador del proyecto
     * @return JsonResponse JSON con mensaje de respuesta
     */
    public function deleteAttachmentAction(Request $request, $id) {
        $response = array('result' => '__OK__', 'msg' => '');

        $em = $this->getDoctrine()->getManager();
        $attachmentId = $request->request->get('attachId');
        $attachment = $em->getRepository('BackendBundle:ItemAttachment')->find($attachmentId);

        if (!$attachment || ($attachment && $attachment->getItem()->getProject()->getId() != $id)) {
            $response['result'] = '__KO__';
            $response['msg'] = $this->get('translator')->trans('backend.attachment.not_found');
            return new JsonResponse($response);
        }

        if (!$this->container->get('access_control')->isAllowedProject($id)) {
            $response['result'] = '__KO__';
            $response['msg'] = $this->get('translator')->trans('backend.project.not_found_message');
            return new JsonResponse($response);
        }

        try {
            $directory = $this->container->getParameter('item_attachments_folder');
            unlink($directory . $attachment->getFilePath());

            $em->remove($attachment);
            $em->flush();

            //guardamos el registro en el historial
            $this->container->get('app_history')->saveItemHistory($attachment->getItem(), Entity\ItemHistory::ITEM_ATTACHMENT_DELETED, null, ': <strong>' . $attachment->getName() . '</strong>');
        } catch (\Exception $ex) {
            $response['result'] = '__KO__';
            $response['msg'] = $this->get('translator')->trans('backend.global.unknown_error');
        }

        return new JsonResponse($response);
    }

    /**
     * Permite crear un item en el sistema
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 21/01/2016
     * @param Request $request
     * @param type $id
     * @param type $itemId
     * @return type
     */
    public function newRelatedItemAction(Request $request, $id, $itemId) {
        $em = $this->getDoctrine()->getManager();

        $project = $em->getRepository('BackendBundle:Project')->find($id);
        $item = $em->getRepository('BackendBundle:Item')->find($itemId);

        if (!$project || ($project && !$this->container->get('access_control')->isAllowedProject($project->getId()))) {
            $this->get('session')->getFlashBag()->add('messageError', $this->get('translator')->trans('backend.project.not_found_message'));
            return $this->redirectToRoute('backend_projects');
        }

        if (!$item || ($item && $item->getProject()->getId() != $project->getId())) {
            $this->get('session')->getFlashBag()->add('messageError', $this->get('translator')->trans('backend.item.not_found_message'));
            return $this->redirectToRoute('backend_projects');
        }

        $closeFancy = false;
        $relatedItem = new Entity\Item();
        $relatedItem->setType(Entity\Item::TYPE_TASK);
        $form = $this->createForm(ItemSimpleType::class, $relatedItem);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $relatedItem->setProject($project);
            $relatedItem->setSprint($item->getSprint());
            $relatedItem->setParent($item);
            $relatedItem->setStatus(Entity\Item::STATUS_NEW);
            $relatedItem->setUserOwner($this->getUser());

            $em->persist($relatedItem);
            $em->flush();

            //guardamos el registro en el historial
            $this->container->get('app_history')->saveItemHistory($relatedItem, Entity\ItemHistory::ITEM_CREATED);


            if ($form->get('saveAndContinue')->isClicked()) {
                $params = array('id' => $id, 'itemId' => $itemId);
                return $this->redirectToRoute('backend_project_product_backlog_new_related_item', $params);
            } elseif ($form->get('saveAndExit')->isClicked()) {
                $this->get('session')->getFlashBag()->add('messageSuccess', $this->get('translator')->trans('backend.item.creation_success_message'));
                $closeFancy = true;
            }
        }

        return $this->render('BackendBundle:Project/ProductBacklog:newRelatedItem.html.twig', array(
                    'project' => $project,
                    'item' => $item,
                    'form' => $form->createView(),
                    'menu' => self::MENU,
                    'closeFancy' => $closeFancy,
        ));
    }

    /**
     * Esta funcion permite realizar la eliminacion de un archivo, 
     * tando del registro en base de datos como de la carpeta donde 
     * se encuentra alojado
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 02/10/2016
     * @param Request $request datos de la solicitud
     * @param string $id identificador del proyecto
     * @return JsonResponse JSON con mensaje de respuesta
     */
    public function deleteAction(Request $request, $id) {
        $response = array('result' => '__OK__', 'msg' => $this->get('translator')->trans('backend.item.delete_success_message'));
        $em = $this->getDoctrine()->getManager();
        $itemId = $request->request->get('itemId');
        $deleteMode = $request->request->get('mode');
        $item = $em->getRepository('BackendBundle:Item')->find($itemId);

        if (!$item || ($item && $item->getProject()->getId() != $id)) {
            $response['result'] = '__KO__';
            $response['msg'] = $this->get('translator')->trans('backend.item.not_found_message');
            return new JsonResponse($response);
        }

        if (!$this->container->get('access_control')->isAllowedProject($id)) {
            $response['result'] = '__KO__';
            $response['msg'] = $this->get('translator')->trans('backend.project.not_found_message');
            return new JsonResponse($response);
        }

        $directory = $this->container->getParameter('item_attachments_folder');

        try {
            //verificamos el modo de eliminacion
            if ($deleteMode == Entity\Item::DELETE_CASCADE) {

                //buscamos los hijos del item, ponemos en null la relacion con otros
                //items, eliminamos los attachments y luego los eliminamos los items
                $children = $em->getRepository('BackendBundle:Item')->findAllChildren($itemId);
                foreach ($children as $child) {

                    $attachments = $em->getRepository('BackendBundle:ItemAttachment')->findByItem($child->getId());
                    foreach ($attachments as $attach) {
                        if (file_exists($directory . $attach->getFilePath())) {
                            unlink($directory . $attach->getFilePath());
                        }
                    }

                    $child->setParent(null);
                    $em->persist($child);
                }
                $em->flush();

                foreach ($children as $child) {
                    $em->remove($child);
                }
            } elseif ($deleteMode == Entity\Item::DELETE_SIMPLE) {
                //buscamos los items que tengan a este item como padre, para desasignarlo
                foreach ($item->getChildren() as $child) {
                    $child->setParent(null);
                    $em->persist($child);
                }
            }
            //borramos el item y sus attachments
            $attachments = $em->getRepository('BackendBundle:ItemAttachment')->findByItem($item->getId());
            foreach ($attachments as $attach) {
                if (file_exists($directory . $attach->getFilePath())) {
                    unlink($directory . $attach->getFilePath());
                }
            }
            $em->remove($item);
            $em->flush();
        } catch (\Exception $ex) {
            $response['result'] = '__KO__';
            $response['msg'] = $this->get('translator')->trans('backend.global.unknown_error');
        }

        return new JsonResponse($response);
    }

    /**
     * Permite editar la prioridad de un item
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 02/15/2016
     * @param Request $request datos de la solicitud
     * @param string $id identificador del proyecto
     * @return JsonResponse JSON con mensaje de respuesta
     */
    public function changePriorityAction(Request $request, $id) {
        $response = array('result' => '__OK__', 'msg' => $this->get('translator')->trans('backend.item.update_success_message'));
        $em = $this->getDoctrine()->getManager();
        $itemId = $request->request->get('itemId');
        $priority = $request->request->get('priority');
        $item = $em->getRepository('BackendBundle:Item')->find($itemId);
        $previousPriority = $item->getPriority();

        if (!$item || ($item && $item->getProject()->getId() != $id)) {
            $response['result'] = '__KO__';
            $response['msg'] = $this->get('translator')->trans('backend.item.not_found_message');
            return new JsonResponse($response);
        }

        if (!$this->container->get('access_control')->isAllowedProject($id)) {
            $response['result'] = '__KO__';
            $response['msg'] = $this->get('translator')->trans('backend.project.not_found_message');
            return new JsonResponse($response);
        }

        try {
            $item->setPriority($priority);
            $em->persist($item);
            $em->flush();

            //guardamos el registro en el historial
            $changes = array('before' => $previousPriority, 'after' => $item->getPriority());
            $this->container->get('app_history')->saveItemHistory($item, Entity\ItemHistory::ITEM_PRIORITY_MODIFIED, $changes);
        } catch (\Exception $ex) {
            $response['result'] = '__KO__';
            $response['msg'] = $this->get('translator')->trans('backend.global.unknown_error');
        }

        return new JsonResponse($response);
    }

    /**
     * Permite editar la prioridad de un item
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 02/15/2016
     * @param Request $request datos de la solicitud
     * @param string $id identificador del proyecto
     * @return JsonResponse JSON con mensaje de respuesta
     */
    public function changeParentAction(Request $request, $id) {
        $response = array('result' => '__OK__', 'msg' => $this->get('translator')->trans('backend.item.update_success_message'));
        $em = $this->getDoctrine()->getManager();
        $itemId = $request->request->get('itemId');
        $parentId = $request->request->get('newParent');
        $item = $em->getRepository('BackendBundle:Item')->find($itemId);

        $parent = null;
        if ($parentId != Entity\Item::EMPTY_PARENT) {
            $parent = $em->getRepository('BackendBundle:Item')->find($parentId);
        }

        if (!$item || ($item && $item->getProject()->getId() != $id)) {
            $response['result'] = '__KO__';
            $response['msg'] = $this->get('translator')->trans('backend.item.not_found_message');
            return new JsonResponse($response);
        }

        if ($parentId != Entity\Item::EMPTY_PARENT && (!$parent || ($parent && $parent->getProject()->getId() != $id))) {
            $response['result'] = '__KO__';
            $response['msg'] = $this->get('translator')->trans('backend.item.not_found_message');
            return new JsonResponse($response);
        }

        if (!$this->container->get('access_control')->isAllowedProject($id)) {
            $response['result'] = '__KO__';
            $response['msg'] = $this->get('translator')->trans('backend.project.not_found_message');
            return new JsonResponse($response);
        }

        try {
            $item->setParent($parent);
            $em->persist($item);
            $em->flush();
        } catch (\Exception $ex) {
            $response['result'] = '__KO__';
            $response['msg'] = $this->get('translator')->trans('backend.global.unknown_error');
        }

        return new JsonResponse($response);
    }

    /**
     * Permite obtener el html necesario para editar la estimacion de un item
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 02/15/2016
     * @param Request $request
     * @param type $id
     * @return JsonResponse
     */
    public function getHtmlEditEstimationAction(Request $request, $id) {
        $response = array('result' => '__OK__', 'html' => '');

        $itemId = $request->request->get('itemId');
        $estimation = $request->request->get('estimation');

        $html = $this->renderView('BackendBundle:Project/ProductBacklog:editEstimationForm.html.twig', array(
            'itemId' => $itemId,
            'estimation' => $estimation,
        ));
        $response['html'] = $html;

        return new JsonResponse($response);
    }

    /**
     * Permite editar la estimacion de un item
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 02/15/2016
     * @param Request $request datos de la solicitud
     * @param string $id identificador del proyecto
     * @return JsonResponse JSON con mensaje de respuesta
     */
    public function editEstimationAction(Request $request, $id) {
        $response = array('result' => '__OK__', 'msg' => $this->get('translator')->trans('backend.item.update_success_message'));
        $em = $this->getDoctrine()->getManager();
        $itemId = $request->request->get('itemId');
        $estimation = $request->request->get('estimation');
        $item = $em->getRepository('BackendBundle:Item')->find($itemId);
        $previousEstimation = $item->getEstimatedHours();

        if (!$item || ($item && $item->getProject()->getId() != $id)) {
            $response['result'] = '__KO__';
            $response['msg'] = $this->get('translator')->trans('backend.item.not_found_message');
            return new JsonResponse($response);
        }

        if (!$this->container->get('access_control')->isAllowedProject($id)) {
            $response['result'] = '__KO__';
            $response['msg'] = $this->get('translator')->trans('backend.project.not_found_message');
            return new JsonResponse($response);
        }

        try {
            $item->setEstimatedHours($estimation);
            $em->persist($item);
            $em->flush();

            //guardamos el registro en el historial
            $changes = array('before' => $previousEstimation, 'after' => $item->getEstimatedHours());
            $this->container->get('app_history')->saveItemHistory($item, Entity\ItemHistory::ITEM_ESTIMATED_HOURS_MODIFIED, $changes);
        } catch (\Exception $ex) {
            $response['result'] = '__KO__';
            $response['msg'] = $this->get('translator')->trans('backend.global.unknown_error');
        }

        return new JsonResponse($response);
    }

    /**
     * Permite obtener el html necesario para editar el tiempo invertido en un item
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 02/15/2016
     * @param Request $request
     * @param type $id
     * @return JsonResponse
     */
    public function getHtmlEditWorkedTimeAction(Request $request, $id) {
        $response = array('result' => '__OK__', 'html' => '');

        $itemId = $request->request->get('itemId');
        $workedTime = $request->request->get('workedTime');

        $html = $this->renderView('BackendBundle:Project/ProductBacklog:editWorkedTimeForm.html.twig', array(
            'itemId' => $itemId,
            'workedTime' => $workedTime,
        ));
        $response['html'] = $html;

        return new JsonResponse($response);
    }

    /**
     * Permite editar el tiempo trabajado en un item
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 02/15/2016
     * @param Request $request datos de la solicitud
     * @param string $id identificador del proyecto
     * @return JsonResponse JSON con mensaje de respuesta
     */
    public function editWorkedTimeAction(Request $request, $id) {
        $response = array('result' => '__OK__', 'msg' => $this->get('translator')->trans('backend.item.update_success_message'));
        $em = $this->getDoctrine()->getManager();
        $itemId = $request->request->get('itemId');
        $workedTime = $request->request->get('workedTime');
        $item = $em->getRepository('BackendBundle:Item')->find($itemId);
        $previousWorked = $item->getWorkedHours();

        if (!$item || ($item && $item->getProject()->getId() != $id)) {
            $response['result'] = '__KO__';
            $response['msg'] = $this->get('translator')->trans('backend.item.not_found_message');
            return new JsonResponse($response);
        }

        if (!$this->container->get('access_control')->isAllowedProject($id)) {
            $response['result'] = '__KO__';
            $response['msg'] = $this->get('translator')->trans('backend.project.not_found_message');
            return new JsonResponse($response);
        }

        try {
            $item->setWorkedHours($workedTime);
            $em->persist($item);
            $em->flush();

            //guardamos el registro en el historial
            $changes = array('before' => $previousWorked, 'after' => $item->getWorkedHours());
            $this->container->get('app_history')->saveItemHistory($item, Entity\ItemHistory::ITEM_WORKED_HOURS_MODIFIED, $changes);
        } catch (\Exception $ex) {
            $response['result'] = '__KO__';
            $response['msg'] = $this->get('translator')->trans('backend.global.unknown_error');
        }

        return new JsonResponse($response);
    }

    /**
     * Permite obtener el html necesario para editar el estado de un item
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 02/16/2016
     * @param Request $request
     * @param type $id
     * @return JsonResponse
     */
    public function getHtmlChangeStatusAction(Request $request, $id) {
        $response = array('result' => '__OK__', 'html' => '');

        $itemId = $request->request->get('itemId');
        $status = $request->request->get('status');

        $html = $this->renderView('BackendBundle:Project/ProductBacklog:editStatusForm.html.twig', array(
            'itemId' => $itemId,
            'status' => $status,
            'statusList' => $this->get('form_helper')->getItemStatusOptions(),
        ));
        $response['html'] = $html;

        return new JsonResponse($response);
    }

    /**
     * Permite editar el estado de un item
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 02/16/2016
     * @param Request $request datos de la solicitud
     * @param string $id identificador del proyecto
     * @return JsonResponse JSON con mensaje de respuesta
     */
    public function changeStatusAction(Request $request, $id) {
        $response = array('result' => '__OK__', 'msg' => $this->get('translator')->trans('backend.item.update_success_message'));
        $em = $this->getDoctrine()->getManager();
        $itemId = $request->request->get('itemId');
        $status = $request->request->get('status');
        $item = $em->getRepository('BackendBundle:Item')->find($itemId);
        $previousStatus = $this->container->get('translator')->trans($item->getTextStatus());

        if (!$item || ($item && $item->getProject()->getId() != $id)) {
            $response['result'] = '__KO__';
            $response['msg'] = $this->get('translator')->trans('backend.item.not_found_message');
            return new JsonResponse($response);
        }

        if (!$this->container->get('access_control')->isAllowedProject($id)) {
            $response['result'] = '__KO__';
            $response['msg'] = $this->get('translator')->trans('backend.project.not_found_message');
            return new JsonResponse($response);
        }

        try {
            $item->setStatus($status);

            //verificamos los ciclos de correccion del item
            if ($previousStatus != $status && $status == Entity\Item::STATUS_READY_FOR_TESTING) {
                $item->setFixedOnCycle($item->getFixedOnCycle() + 1);
            }

            $em->persist($item);
            $em->flush();

            //guardamos el registro en el historial
            $newStatus = $this->container->get('translator')->trans($item->getTextStatus());
            $changes = array('before' => $previousStatus, 'after' => $newStatus);
            $this->container->get('app_history')->saveItemHistory($item, Entity\ItemHistory::ITEM_STATUS_MODIFIED, $changes);
        } catch (\Exception $ex) {
            $response['result'] = '__KO__';
            $response['msg'] = $this->get('translator')->trans('backend.global.unknown_error');
        }

        return new JsonResponse($response);
    }

    /**
     * Esta funcion permite validar y realizar el proceso de copiar o mover items 
     * entre Sprints, ya sea mover o copiar solo el item, o tambien mover o copiar
     * todos los items dependientes del mismo
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 14/03/2016
     * @param Request $request
     * @param string $id identificador del proyecto
     * @param string $itemId identificador del item
     * @return type
     */
    public function copyMoveToSprintAction(Request $request, $id, $itemId) {
        $em = $this->getDoctrine()->getManager();

        $project = $em->getRepository('BackendBundle:Project')->find($id);
        $item = $em->getRepository('BackendBundle:Item')->find($itemId);
        $previousSprint = $item->getSprint();

        if (!$project || ($project && !$this->container->get('access_control')->isAllowedProject($project->getId()))) {
            $this->get('session')->getFlashBag()->add('messageError', $this->get('translator')->trans('backend.project.not_found_message'));
            return $this->redirectToRoute('backend_projects');
        }

        if (!$item || ($item && $item->getProject()->getId() != $project->getId())) {
            $this->get('session')->getFlashBag()->add('messageError', $this->get('translator')->trans('backend.item.not_found_message'));
            return $this->redirectToRoute('backend_projects');
        }

        $closeFancy = false;
        $form = $this->createForm(MoveItemToSprintType::class, $item);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $parameters = $request->request->get('backendbundle_item_move_to_sprint_type');
            if (isset($parameters['action']) && isset($parameters['method']) && isset($parameters['new_sprint'])) {
                $action = $parameters['action'];
                $method = $parameters['method'];
                $sprint = $em->getRepository('BackendBundle:Sprint')->find($parameters['new_sprint']);

                if ($action == MoveItemToSprintType::MOVE_TO_SPRINT) {

                    $this->changeSprintToItem($item, $previousSprint, $sprint, null);

                    if ($method == MoveItemToSprintType::ACTION_METHOD_SIMPLE) {
                        foreach ($item->getChildren() as $child) {
                            $child->setParent(null);
                            $em->persist($child);
                        }
                        $em->flush();
                    }

                    if ($method == MoveItemToSprintType::ACTION_METHOD_CASCADE) {
                        foreach ($item->getChildren() as $child) {
                            $this->changeSprintToItem($child, $child->getSprint(), $sprint, $item);
                        }
                    }
                    $closeFancy = true;
                } elseif ($action == MoveItemToSprintType::COPY_TO_SPRINT) {

                    $newItem = $this->copyItemToSprint($item, $previousSprint, $sprint, null);

                    if ($method == MoveItemToSprintType::ACTION_METHOD_CASCADE) {
                        foreach ($item->getChildren() as $child) {
                            $this->copyItemToSprint($child, $child->getSprint(), $sprint, $newItem);
                        }
                    }
                    $closeFancy = true;
                }
            }
        }

        return $this->render('BackendBundle:Project/ProductBacklog:copyMoveToSprint.html.twig', array(
                    'project' => $project,
                    'item' => $item,
                    'form' => $form->createView(),
                    'menu' => self::MENU,
                    'closeFancy' => $closeFancy,
        ));
    }

    /**
     * Permite mover un item de un Sprint a otro y realizar en el historial
     * el registro de la operacion
     * @author Cesar Giraldo <cnaranjo@kijho.com> 13/03/2015
     * @param Entity\Item $item
     * @param Entity\Sprint|null $previousSprint
     * @param Entity\Sprint|null $sprint
     * @param Entity\Item|null $parent
     */
    private function changeSprintToItem($item, $previousSprint, $sprint, $parent) {
        $em = $this->getDoctrine()->getManager();
        //cambiamos el Sprint del item
        $item->setSprint($sprint);
        $item->setParent($parent);
        $em->persist($item);
        $em->flush();

        //guardamos el registro en historial
        if (!$previousSprint && $sprint) {
            $changes = array('before' => $this->get('translator')->trans('backend.item.no_sprint'), 'after' => $sprint . "");
            $this->container->get('app_history')->saveItemHistory($item, Entity\ItemHistory::ITEM_SPRINT_ASSIGNED, $changes, " : " . $sprint);
        } elseif ($previousSprint && $sprint) {
            $changes = array('before' => $previousSprint . "", 'after' => $sprint . "");
            $this->container->get('app_history')->saveItemHistory($item, Entity\ItemHistory::ITEM_SPRINT_MOVED, $changes, " : " . $sprint);
        }
    }

    /**
     * Permite copiar un item de un Sprint a otro y realizar en el historial
     * el registro de la operacion
     * @author Cesar Giraldo <cnaranjo@kijho.com> 13/03/2015
     * @param Entity\Item $item
     * @param Entity\Sprint|null $previousSprint
     * @param Entity\Sprint|null $sprint
     * @param Entity\Item|null $parent
     */
    private function copyItemToSprint($item, $previousSprint, $sprint, $parent) {
        $em = $this->getDoctrine()->getManager();
        //Duplicamos el item y le asignamos el nuevo Sprint
        $newItem = clone $item;
        $newItem->setSprint($sprint);
        $newItem->setParent($parent);
        $newItem->setFixedOnCycle(null);
        $em->persist($newItem);
        $em->flush();

        //almacenamos el evento en el historial
        if (!$previousSprint && $sprint) {
            $changes = array('before' => $this->get('translator')->trans('backend.item.no_sprint'), 'after' => $sprint . "");
            $this->container->get('app_history')->saveItemHistory($newItem, Entity\ItemHistory::ITEM_SPRINT_COPIED, $changes, " : " . $sprint);
        } elseif ($previousSprint && $sprint) {
            $changes = array('before' => $previousSprint . "", 'after' => $sprint . "");
            $this->container->get('app_history')->saveItemHistory($newItem, Entity\ItemHistory::ITEM_SPRINT_COPIED, $changes, " : " . $sprint);
        }
        return $newItem;
    }

    /**
     * Esta funcion permite validar y realizar el proceso de copiar o mover items 
     * entre Proyectos, ya sea mover o copiar solo el item, o tambien mover o copiar
     * todos los items dependientes del mismo
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 14/03/2016
     * @param Request $request
     * @param string $id identificador del proyecto
     * @param string $itemId identificador del item
     * @return type
     */
    public function copyMoveToProjectAction(Request $request, $id, $itemId) {
        $em = $this->getDoctrine()->getManager();

        $project = $em->getRepository('BackendBundle:Project')->find($id);
        $item = $em->getRepository('BackendBundle:Item')->find($itemId);
        $previousProject = $item->getProject();

        if (!$project || ($project && !$this->container->get('access_control')->isAllowedProject($project->getId()))) {
            $this->get('session')->getFlashBag()->add('messageError', $this->get('translator')->trans('backend.project.not_found_message'));
            return $this->redirectToRoute('backend_projects');
        }

        if (!$item || ($item && $item->getProject()->getId() != $project->getId())) {
            $this->get('session')->getFlashBag()->add('messageError', $this->get('translator')->trans('backend.item.not_found_message'));
            return $this->redirectToRoute('backend_projects');
        }

        $closeFancy = false;
        $form = $this->createForm(MoveItemToProjectType::class, $item);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $parameters = $request->request->get('backendbundle_item_move_to_project_type');
            if (isset($parameters['action']) && isset($parameters['method']) && isset($parameters['user_project'])) {
                $action = $parameters['action'];
                $method = $parameters['method'];
                $userProject = $em->getRepository('BackendBundle:UserProject')->find($parameters['user_project']);
                $newProject = null;
                if ($userProject instanceof Entity\UserProject) {
                    $newProject = $userProject->getProject();
                }


                if ($action == MoveItemToProjectType::MOVE_TO_PROJECT && $newProject) {

                    $this->changeProjectToItem($item, $previousProject, $newProject, null);

                    if ($method == MoveItemToProjectType::ACTION_METHOD_SIMPLE) {
                        foreach ($item->getChildren() as $child) {
                            $child->setParent(null);
                            $em->persist($child);
                        }
                        $em->flush();
                    }

                    if ($method == MoveItemToProjectType::ACTION_METHOD_CASCADE) {
                        foreach ($item->getChildren() as $child) {
                            $this->changeProjectToItem($child, $child->getProject(), $newProject, $item);
                        }
                    }
                    $closeFancy = true;
                } elseif ($action == MoveItemToProjectType::COPY_TO_PROJECT && $newProject) {

                    $newItem = $this->copyItemToProject($item, $previousProject, $newProject, null);

                    if ($method == MoveItemToProjectType::ACTION_METHOD_CASCADE) {
                        foreach ($item->getChildren() as $child) {
                            $this->copyItemToProject($child, $child->getProject(), $newProject, $newItem);
                        }
                    }
                    $closeFancy = true;
                }
            }
        }

        return $this->render('BackendBundle:Project/ProductBacklog:copyMoveToProject.html.twig', array(
                    'project' => $project,
                    'item' => $item,
                    'form' => $form->createView(),
                    'menu' => self::MENU,
                    'closeFancy' => $closeFancy,
        ));
    }

    /**
     * Esta funcion permite cambiarle el proyecto a un elemento
     * y registrar el evento en un historial
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 14/03/2016
     * @param Entity\Item $item
     * @param Entity\Project $previousProject
     * @param Entity\Project $newProject
     * @param Entity\Item|null $parent
     */
    private function changeProjectToItem($item, $previousProject, $newProject, $parent) {
        $em = $this->getDoctrine()->getManager();
        //cambiamos el Proyecto del item
        $item->setSprint(null);
        $item->setParent($parent);
        $item->setProject($newProject);
        $item->setFixedOnCycle(null);
        $em->persist($item);
        $em->flush();

        //guardamos el registro en historial
        $changes = array('before' => $previousProject . "", 'after' => $newProject . "");
        $this->container->get('app_history')->saveItemHistory($item, Entity\ItemHistory::ITEM_PROJECT_MOVED, $changes, " : " . $newProject);
    }

    /**
     * Esta funcion permite duplicar un item en otro proyecto
     * y almacenar el evento en el historial del item
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 14/03/2016
     * @param Entity\Item $item
     * @param Entity\Project $previousProject
     * @param Entity\Project $newProject
     * @param Entity\Item|null $parent
     * @return Entity\Item
     */
    public function copyItemToProject($item, $previousProject, $newProject, $parent) {
        $em = $this->getDoctrine()->getManager();
        //Duplicamos el item y le asignamos el nuevo Sprint
        $newItem = clone $item;
        $newItem->setSprint(null);
        $newItem->setParent($parent);
        $newItem->setProject($newProject);
        $newItem->setFixedOnCycle(null);
        $em->persist($newItem);
        $em->flush();

        //almacenamos el evento en el historial
        $changes = array('before' => $previousProject . "", 'after' => $newProject . "");
        $this->container->get('app_history')->saveItemHistory($newItem, Entity\ItemHistory::ITEM_PROJECT_COPIED, $changes, " : " . $newProject);
        return $newItem;
    }

    /**
     * Permite mover un item al Product Backlog
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 21/03/2016
     * @param Request $request datos de la solicitud
     * @param string $id identificador del proyecto
     * @return JsonResponse JSON con mensaje de respuesta
     */
    public function moveToProductBacklogAction(Request $request, $id) {
        $response = array('result' => '__OK__', 'msg' => $this->get('translator')->trans('backend.item.update_success_message'));
        $em = $this->getDoctrine()->getManager();
        $itemId = $request->request->get('itemId');
        $item = $em->getRepository('BackendBundle:Item')->find($itemId);
        $previousSprint = $item->getSprint() . "";

        if (!$item || ($item && $item->getProject()->getId() != $id)) {
            $response['result'] = '__KO__';
            $response['msg'] = $this->get('translator')->trans('backend.item.not_found_message');
            return new JsonResponse($response);
        }

        if (!$this->container->get('access_control')->isAllowedProject($id)) {
            $response['result'] = '__KO__';
            $response['msg'] = $this->get('translator')->trans('backend.project.not_found_message');
            return new JsonResponse($response);
        }

        try {
            $item->setSprint(null);
            $item->setParent(null);
            $item->setFixedOnCycle(null);
            $em->persist($item);
            $em->flush();

            //guardamos el registro en el historial
            $changes = array('before' => $previousSprint, 'after' => '~');
            $this->container->get('app_history')->saveItemHistory($item, Entity\ItemHistory::ITEM_MOVED_TO_PRODUCT_BACKLOG, $changes);
        } catch (\Exception $ex) {
            $response['result'] = '__KO__';
            $response['msg'] = $this->get('translator')->trans('backend.global.unknown_error');
        }

        return new JsonResponse($response);
    }

}
