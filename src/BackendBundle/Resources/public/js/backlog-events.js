/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
$(document).ready(function () {
    /**
     * Codigo para eventod drag and drop en el backlog
     */
    if (!is_mobile_device) {
        $("ol.vertical").sortable({
            group: 'simple_with_animation',
            pullPlaceholder: true,
            // animation on drop
            // item es el elemento que estamos arrastrando
            onDrop: function ($item, container, _super) {

                var $clonedItem = $('<li/>').css({height: 0});
                $item.before($clonedItem);
                $clonedItem.animate({'height': $item.height()});

                $item.animate($clonedItem.position(), function () {
                    $clonedItem.detach();
                    _super($item, container);
                    //container el es el contenedor en donde se esta moviendo el elemento
                    moveItem($item, container);
                });
            },
            // set $item relative to cursor position
            onDragStart: function ($item, container, _super) {
                var offset = $item.offset(),
                        pointer = container.rootGroup.pointer;

                adjustment = {
                    left: pointer.left - offset.left,
                    top: pointer.top - offset.top
                };

                _super($item, container);
            },
            //item es el elemento que estamos arrastrando
            onDrag: function ($item, position) {
                $item.css({
                    left: position.left - adjustment.left,
                    top: position.top - adjustment.top
                });
            }
        });
    }

    $(".create-related-item").fancybox({
        width: '840px',
        height: '500px',
        autoSize: true,
        autoScale: false,
        autoDimensions: false,
    });

    $(".copy-move-to-sprint").fancybox({
        width: '500px',
        height: '350px',
        autoSize: true,
        autoScale: false,
        autoDimensions: false,
    });

    $(".copy-move-to-project").fancybox({
        width: '500px',
        height: '350px',
        autoSize: true,
        autoScale: false,
        autoDimensions: false,
    });

    /** 
     * Codigo para cambiar la prioridad de los items 
     **/
    $(".change-priority").click(function () {
        var object = $(this);
        var itemId = object.attr('item-id');
        var priority = object.attr('priority');

        bootbox.prompt({
            title: changePriorityTitle,
            value: priority,
            buttons: {
                confirm: {
                    label: changePriorityLabel,
                    className: "btn-primary",
                },
                cancel: {
                    label: changePriorityCancel,
                    className: "btn-default",
                }
            },
            callback: function (result) {
                if (typeof (result) == 'string') {
                    if (result != '') {
                        var newPriority = parseInt(result);
                        if (newPriority >= 0 && newPriority <= 100) {
                            modifyPriority(newPriority, itemId, true);
                        } else {
                            $(".bootbox-input").select();
                            return false;
                        }
                    } else if (result !== null) {
                        $(".bootbox-input").select();
                        return false;
                    }
                }
            }
        });
    });

    /**
     * Codigo para que el cliente pueda eliminar items
     */
    $(".delete-item").click(function () {
        var object = $(this);
        var itemId = object.attr('item-id');

        bootbox.dialog({
            message: deleteItemMessage,
            title: deleteItemTitle,
            buttons: {
                success: {
                    label: cascadeDeleteLabel,
                    className: "btn-danger",
                    callback: function () {
                        deleteItem(itemId, cascadeDeleteConstant);
                    }
                },
                danger: {
                    label: simpleDeleteLabel,
                    className: "btn-danger",
                    callback: function () {
                        deleteItem(itemId, simpleDeleteConstant);
                    }
                },
                main: {
                    label: cancelLabel,
                    className: "btn-default",
                    callback: function () {
                    }
                }
            }
        });
    });

    /**
     * Codigo para cambiar la estimacion de los items
     */
    $(".edit-estimation").click(function () {
        var object = $(this);
        var itemId = object.attr('item-id');
        var estimation = object.attr('estimation');

        var htmlDialog = '';
        $.ajax({
            type: 'POST',
            url: editEstimationHtmlPath,
            dataType: 'json',
            data: {itemId: itemId, estimation: estimation},
            async: false,
            success: function (resp)
            {
                if (resp.result == '__OK__') {
                    htmlDialog = resp.html;
                } else {
                    bootbox.alert(resp.msg);
                }
            }
        });

        bootbox.dialog({
            title: editEstimationTitle,
            message: htmlDialog,
            buttons: {
                success: {
                    label: editEstimationLabel,
                    className: "btn-primary",
                    callback: function () {
                        var newEstimation = $('#estimated-hours-' + itemId).val();
                        if (newEstimation != '') {
                            var newEstimation = parseFloat(newEstimation);
                            if (newEstimation >= 0) {
                                $.ajax({
                                    type: 'POST',
                                    url: editEstimationPath,
                                    dataType: 'json',
                                    data: {itemId: itemId, estimation: newEstimation},
                                    success: function (r)
                                    {
                                        if (r.result == '__OK__') {
                                            window.location.reload();
                                        } else {
                                            bootbox.alert(r.msg);
                                        }
                                    },
                                    error: function (r)
                                    {
                                        bootbox.alert(unknownErrorMessage)
                                    }
                                });
                            } else {
                                $('#estimated-hours-' + itemId).select();
                                return false;
                            }
                        } else {
                            $('#estimated-hours-' + itemId).select();
                            return false;
                        }
                    }
                },
                main: {
                    label: cancelLabel,
                    className: "btn-default",
                    callback: function () {

                    }
                }
            }
        });
    });

    /**
     * Codigo para cambiar el estado de un item
     */
    $(".change-status").click(function () {
        var object = $(this);
        var itemId = object.attr('item-id');
        var status = object.attr('status');

        var htmlDialog = '';
        $.ajax({
            type: 'POST',
            url: changeStatusHtmlPath,
            dataType: 'json',
            data: {itemId: itemId, status: status},
            async: false,
            success: function (resp)
            {
                if (resp.result == '__OK__') {
                    htmlDialog = resp.html;
                } else {
                    bootbox.alert(resp.msg);
                }
            }
        });

        bootbox.dialog({
            title: changeStatusTitle,
            message: htmlDialog,
            buttons: {
                success: {
                    label: saveChangesLabel,
                    className: "btn-primary",
                    callback: function () {
                        var newStatus = $('#item-status-' + itemId).val();
                        if (newStatus != '') {
                            var newStatus = parseInt(newStatus);
                            if (newStatus > 0) {
                                $.ajax({
                                    type: 'POST',
                                    url: changeStatusPath,
                                    dataType: 'json',
                                    data: {itemId: itemId, status: newStatus},
                                    success: function (r)
                                    {
                                        if (r.result == '__OK__') {
                                            window.location.reload();
                                        } else {
                                            bootbox.alert(r.msg);
                                        }
                                    },
                                    error: function (r)
                                    {
                                        bootbox.alert(unknownErrorMessage)
                                    }
                                });
                            } else {
                                $('#item-status-' + itemId).focus();
                                return false;
                            }
                        } else {
                            $('#item-status-' + itemId).focus();
                            return false;
                        }
                    }
                },
                main: {
                    label: cancelLabel,
                    className: "btn-default",
                    callback: function () {

                    }
                }
            }
        });
    });

    /**
     * Codigo para cambiar el tiempo trabajado en un item
     */
    $(".edit-worked-time").click(function () {
        var object = $(this);
        var itemId = object.attr('item-id');
        var workedTime = object.attr('worked-time');

        var htmlDialog = '';
        $.ajax({
            type: 'POST',
            url: editWorkedTimeHtmlPath,
            dataType: 'json',
            data: {itemId: itemId, workedTime: workedTime},
            async: false,
            success: function (resp)
            {
                if (resp.result == '__OK__') {
                    htmlDialog = resp.html;
                } else {
                    bootbox.alert(resp.msg);
                }
            }
        });

        bootbox.dialog({
            title: workedTimeTitle,
            message: htmlDialog,
            buttons: {
                success: {
                    label: saveChangesLabel,
                    className: "btn-primary",
                    callback: function () {
                        var newWorkedTime = $('#worked-hours-' + itemId).val();
                        if (newWorkedTime != '') {
                            var newWorkedTime = parseFloat(newWorkedTime);
                            if (newWorkedTime >= 0) {
                                $.ajax({
                                    type: 'POST',
                                    url: editWorkedTimePath,
                                    dataType: 'json',
                                    data: {itemId: itemId, workedTime: newWorkedTime},
                                    success: function (r)
                                    {
                                        if (r.result == '__OK__') {
                                            window.location.reload();
                                        } else {
                                            bootbox.alert(r.msg);
                                        }
                                    },
                                    error: function (r)
                                    {
                                        bootbox.alert(unknownErrorMessage)
                                    }
                                });
                            } else {
                                $('#worked-hours-' + itemId).select();
                                return false;
                            }
                        } else {
                            $('#worked-hours-' + itemId).select();
                            return false;
                        }
                    }
                },
                main: {
                    label: cancelLabel,
                    className: "btn-default",
                    callback: function () {

                    }
                }
            }
        });
    });
});

/**
 * Funcion para controlar cuando se mueve un item, editar prioridad, dependencias, etc
 * @param {type} newPriority
 * @param {type} itemId
 * @param {type} reloadPage
 * @returns {Boolean}
 */
function moveItem(item, container) {

    var currentParent = item.attr('parent');
    var itemId = item.attr('item-id')
    console.log('current parent ' + currentParent);

    if (currentParent == container.el.attr('parent')) {
        // si el padre es el mismo, significa que se esta editando la prioridad de un item 
        var currentPriority = item.attr('priority');
        console.log('Prioridad actual ' + currentPriority);

        // buscamos la prioridad del item a continuacion
        var nextLi = item.next();
        var nextPriority = parseInt(nextLi.attr('priority'));

        // buscamos la prioridad del anterior item 
        var prevLi = item.prev();
        var previousPriority = parseInt(prevLi.attr('priority'));
        var newPriority = '';

        if (nextPriority >= 0 && previousPriority >= 0) {
            // si existe un item atras y uno adelante, colocamos la prioridad entre ambos 
            newPriority = nextPriority + (previousPriority - nextPriority) / 2;
        } else if (nextPriority >= 0) {
            // Si no hay un item arriba, colocamos la prioridad del siguiente mas 1 
            newPriority = nextPriority + 1;
        } else if (previousPriority >= 0) {
            // Si no hay un item abajo, colocamos la prioridad del anterior menos 1 
            newPriority = previousPriority - 1;
        }

        if (newPriority != '') {
            // enviar ajax para modificar prioridad 
            var success = modifyPriority(newPriority, itemId, false);

            if (success) {
                item.attr('priority', newPriority);
                var containerPriority = item.find("a.container-priority").first();
                containerPriority.attr('priority', newPriority);
                containerPriority.html(newPriority);
                var otherContainerPriority = item.find('ul.backlog-menu li a.change-priority').first();
                otherContainerPriority.attr('priority', newPriority);
            } else {
                window.location.reload();
            }
        }
    } else {
        // si el padre es distinto, significa que se esta moviendo el item a otro item
        console.log('se desea cambiar el padre');
        console.log('se quiere mover el elemento ' + item.attr('item-id') + ' al contenedor ' + container.el.attr('parent'));

        var newParent = container.el.attr('parent');
        if (currentParent != newParent) {
            // enviar ajax para modificar el padre del item
            modifyItemParent(newParent, itemId);

        }

    }
}

/**
 * Esta funcion permite enviar un ajax para modificar la prioridad de un item
 */
function modifyPriority(newPriority, itemId, reloadPage) {
    var success = false;
    $.ajax({
        type: 'POST',
        url: modifyPriorityPath,
        dataType: 'json',
        data: {itemId: itemId, priority: newPriority},
        async: false,
        success: function (r)
        {
            if (r.result == '__OK__') {
                success = true;
                if (reloadPage) {
                    window.location.reload();
                }
            } else {
                bootbox.alert(r.msg);
            }
        },
        error: function (r)
        {
            bootbox.alert(unknownErrorMessage)
        }
    });
    return success;
}

/**
 * Esta funcion permite enviar un ajax para modificar la el padre de un item
 * @param {type} itemId
 * @param {type} mode
 * @returns {undefined}
 */
function modifyItemParent(newParent, itemId) {
    $.ajax({
        type: 'POST',
        url: modifyParentPath,
        dataType: 'json',
        data: {itemId: itemId, newParent: newParent},
        async: false,
        success: function (r)
        {
            if (r.result == '__OK__') {
                window.location.reload();
            } else {
                bootbox.alert(r.msg);
            }
        },
        error: function (r)
        {
            bootbox.alert(unknownErrorMessage);
        }
    });
}

/**
 * Funcion que solicita la eliminacion de un item
 */
function deleteItem(itemId, mode) {
    $.ajax({
        type: 'POST',
        url: deleteItemPath,
        dataType: 'json',
        data: {itemId: itemId, mode: mode},
        success: function (r)
        {
            if (r.result == '__OK__') {
                window.location.reload();
            } else {
                bootbox.alert(r.msg);
            }
        },
        error: function (r)
        {
            bootbox.alert(unknownErrorMessage)
        }
    });
}



