// BX.ready(() => {
//     const promoContainer = document.getElementById('contact-promo')
//     const dropDown = promoContainer.querySelector('.promo-dropdown')
//     const selectedPromo = {}
//
//     promoContainer.querySelectorAll('.dropdown-options-item.checked')
//         .forEach(e =>
//             selectedPromo[e.dataset.itemId] = {
//                 UF_PROMO_ID: e.dataset.itemId,
//                 UF_PROMO_VALUE: e.dataset.itemValue
//             }
//         )
//
//     let onClickOutside = false
//
//
//     const handleClickOutside = (e) => {
//         if (!e.target.closest('.promo-dropdown')) {
//             document.removeEventListener('click', handleClickOutside)
//         }
//     }
//
//
//     dropDown.addEventListener('click', e => {
//         const nodeItem = e.target.closest('.dropdown-options-item')
//         if (nodeItem) {
//             if (nodeItem.classList.contains('.checked')) {
//                 delete selectedPromo[nodeItem.dataset.itemId]
//             } else {
//                 selectedPromo[nodeItem.dataset.itemId] = {
//                     UF_PROMO_ID: nodeItem.dataset.itemId,
//                     UF_PROMO_VALUE: nodeItem.dataset.itemValue
//                 }
//             }
//             nodeItem.classList.toggle('checked')
//         } else {
//             dropDown.classList.toggle('open')
//         }
//
//
//         if (!onClickOutside) {
//             document.addEventListener("click", handleClickOutside)
//             onClickOutside = true
//         }
//     })
//
//
//
//
//
// //
// //     const promoList = promoContainer.querySelector('.promo-list')
// //     const selectedPromoContainer = promoContainer.querySelector('.contact-promo-list')
// //
// //
// //     promoContainer.querySelectorAll('.promo-list-item')
// //         .forEach(e => {
// //             if (selectedPromo[e.dataset.itemId]) e.classList.add('selected')
// //         })
// //
// //
// //     function getSelectedPromoTemplate(promo){
// //         return `
// //         <li
// //                     class="ui-btn ui-btn-link contact-promo-list-item"
// //                     data-item-id="${promo['UF_PROMO_ID']}"
// //                     data-item-value="${promo['UF_PROMO_VALUE']}"
// //             >
// //                 ${promo['UF_PROMO_VALUE']}
// //             </li>
// //         `
// //     }
// //
// //
// //
// //     function updateSelectedList(){
// //         selectedPromoContainer.innerHTML = Object.values(selectedPromo)
// //             .map(e => getSelectedPromoTemplate(e))
// //             .join('')
// //     }
// //
// //
// //
// //
// //     promoList.addEventListener('click', e => {
// //         const promoItem = e.target.closest('.promo-list-item')
// //         if (promoItem) {
// //             const id = promoItem.dataset.itemId
// //             const value = promoItem.dataset.itemValue
// //             if (selectedPromo[id]) {
// //                 delete selectedPromo[id]
// //             } else {
// //                 selectedPromo[id] = {UF_PROMO_ID: id, UF_PROMO_VALUE: value}
// //             }
// //             updateSelectedList()
// //         }
// //     })
// })
