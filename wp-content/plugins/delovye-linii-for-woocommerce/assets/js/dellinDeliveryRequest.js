/**
 * Файл отвечающий за управление интерфейсом в админке.
 * @version 0.2
 * @author BIA-tech
 */

/**
 * Метод отправляющий fetch запрос к серверу.
 * Этот же метод обрабатывает ответ от сервера.
 * @param price_change
 * @param items
 * @return mixed
 */
const doFetch = (orderChange = null, itemId = null) => {
    showLoading();
    let url = dellinVars.url;
    let method = "POST";
    let body = 'action=send_request&mode=try_request&id='+
        dellinVars.postId+
        '&orderChange='+orderChange+
        '&security='+woocommerce_admin_meta_boxes.order_item_nonce+
        '&itemID='+itemId

    let headers = {
        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
    }


    let request = fetch(url, {
        method,
        headers,
        body
    }).then(
        response => {
            if(response.ok){
                return response.json();
            } else {
                alert('Не возможно получить ответ. Откройте консоль нажав F12 разработчика для выяснения причины.');
                console.log('Подробно:'+response);
            }
        })
        .then(result => {

             handlerResponse(result);
        })
}

let handlerResponse = (result) => {
    switch (true) {
        case result.PRICE_CHANGED:
            return isPriceChanged(result);
        break;
        case result.orderUpdate:
            return isOrderUpdated(result);
        break;
        case (typeof (result.errors) == 'string' || typeof (result.errors) == 'Object' ):
            return isErrors(result);
        break;
        case (result.data.state == 'success'):
            return isSuccess(result);
        break;

    }
}

let isSuccess = (result) => {
    alert('Заявка для заказа ' + dellinVars.postId + ' номер заявки:' +result.data.requestID+' . Сейчас страница перезагрузится автоматически через 5 секунд.');

    console.log(result);

    setInterval(()=> location.reload(), 5000);
}

let isErrors = (result) => {
    if( typeof (result.errors) == 'string'){
    alert('Не возможно получить ответ. Откройте консоль нажав F12 разработчика для выяснения причины.');
    console.warn(result.errors);
    } else {
        result.errors.map((error)=> console.warn('['+error.code+'] -'+'['+error.title+']'+'['+error.details+']'))
    }
}

let isOrderUpdated = (result) => {
    console.log(result.orderUpdate);
    doFetch(true);
}
let isPriceChanged = (result) => {
    let price_change = confirm('Внимание! Cтоимость доставки изменилась. Новая стоимость доставки: ' +result.body.price+
        ' р . Изменить стоимость доставки в заказе?');
    console.log('Изменить цену?', price_change);
    if(price_change){
        let itemId =  document.querySelector('.shipping').dataset.order_item_id
        console.log('Item ID Meta', itemId);
        doFetch(price_change, itemId);
    } else {
        alert('Спасибо. Сейчас страница перезагрузится.')
        location.reload();
    }

}

/**
 * Состояние DOM и AJAX по-умолчанию. Прописываем для удобства управлением состояния
 * @param  {boolean} loading - флаг загрузки страницы
 * @param  {boolean} recalc - т.к. мы проверяем изменение цены при создании заявки для предотвращения абуза отслеживаем его состояние тоже
 * @param  {boolean} complete - отслеживаем состояние после создания заявки.
 * @return {object};
 */

let state = {
    loading: false,
    recalc: false,
    complete: false
}

/**
 * Метод для получения модального окна.
 * @param {object} popUpId - для гибкости вынесен в переменную
 * 
 */

let popUpId = "container";
let getElementPopUp = () => document.getElementById(popUpId);

/**
 * Метод для поддержвания принципа DRY
 * Т.к. мы за лаконичный код и вообще пальцы не казённые.
 */

const createElement = (el) =>  document.createElement(el);

/**
 * Методы для манипулирования DOM дерева в частности защиты области ввода при загрузке окна.
 * 
 * @return {object}
 */


let showLoading = () => {
    let loadingWrapDiv = createElement('div');
        loadingWrapDiv.id = 'loading-wrap';
    let loadingDiv = createElement('div');
    let span = createElement('span');
        span.className = 'loading-content';
        span.innerHTML = 'Загрузка...'
        loadingDiv.id = 'loading';
        loadingDiv.append(span);
        loadingWrapDiv.append(loadingDiv);
    let popUpRequest = getElementPopUp();
        popUpRequest.append(loadingWrapDiv);
        console.log(popUpRequest);
    return popUpRequest;
}

let handlerLoading = (loadstate) => {

}

/**
 * Метод для манипулирования DOM дерева в частности снятия защиты области ввода при загрузке окна.
 * @return {void}
 */


let hideLoading = () =>{
    let wrap = document.querySelector('#loading-wrap');
    let load = document.querySelector('#loading');
        wrap.remove()
        load.remove()
}


let createElementModal = (el, className = false, data = false, id = false)=>{

    let element = createElement(el);
        className !== false?element.className = className : null;
        data !== false? element.innerHTML = data : null;
        id !== false?element.id = id : null;

    return element;
}


document.addEventListener('DOMContentLoaded', function() {

    console.log('Init Settings Request');
    console.log(dellinVars);
    console.log(getElementPopUp());

    let button = document.getElementById('dellinSendRequest');
        button.addEventListener('click', (e)=>{
            doFetch(false);
            })
})
