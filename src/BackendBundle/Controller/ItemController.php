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

        return $this->render('BackendBundle:Project/ProductBacklog:edit.html.twig', array(
                    'item' => $item,
                    'attachments' => $attachments,
                    'itemHistory' => $itemHistory,
                    'project' => $item->getProject(),
                    'edit_form' => $editForm->createView(),
                    'menu' => self::MENU,
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
                            $this->container->get('app_history')->saveItemHistory($item, Entity\ItemHistory::ITEM_ATTACHMENT_ADDED, null, $attachment->getName());

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
            $this->container->get('app_history')->saveItemHistory($attachment->getItem(), Entity\ItemHistory::ITEM_ATTACHMENT_DELETED, null, $attachment->getName());
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

            $this->get('session')->getFlashBag()->add('messageSuccess', $this->get('translator')->trans('backend.item.creation_success_message'));
            $closeFancy = true;
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
        $priority = (int) $request->request->get('priority');
        $item = $em->getRepository('BackendBundle:Item')->find($itemId);
        $previousPriority = (int) $item->getPriority();

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
            $changes = array('before' => $previousPriority, 'after' => (int) $item->getPriority());
            $this->container->get('app_history')->saveItemHistory($item, Entity\ItemHistory::ITEM_PRIORITY_MODIFIED, $changes);
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

}
