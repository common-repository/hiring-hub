(()=>{"use strict";const e=(e,t=null)=>{try{return JSON.parse(e)}catch(e){return t}},t=()=>{const t="wp-block-hiring-hub-query-loop-filtering-filter__checkbox",o="wp-block-hiring-hub-query-loop-filtering-filter__checkbox--selected",i="wp-block-hiring-hub-query-loop-filtering-filter__choice",r="wp-block-hiring-hub-query-loop-filtering-filter__choice--selected",l="wp-block-hiring-hub-query-loop-filtering-filter__dropdown--visible",c="wp-block-hiring-hub-query-loop-filtering-filter",n="wp-block-hiring-hub-query-loop-filtering-filter--dropdown",s="wp-block-hiring-hub-query-loop-filtering-filter__radio",u="wp-block-hiring-hub-query-loop-filtering-filter__radio--selected",a=()=>{for(const e of document.querySelectorAll(`.${l}`))e.classList.remove(l)},f=(e,i)=>{const l=e.querySelector(`.${"checkbox"===i?t:s}`);e.classList.add(r),l.classList.add("checkbox"===i?o:u)},d=(e,i)=>{const l=e.querySelector(`.${"checkbox"===i?t:s}`);e.classList.remove(r),l.classList.remove("checkbox"===i?o:u)},b=(e,t)=>{for(const o of e.querySelectorAll(`.${i}`))d(o,t)},h=(t,o=[])=>{if(!t.classList.contains(n))return;const i=t.querySelector(".wp-block-hiring-hub-query-loop-filtering-filter__selection-summary");if(0===o.length)return void(i.textContent="");const r=e(t.dataset.selectionLabelsMapping);let l="";for(const e of o)l+=(""===l?"":", ")+r[e];i.textContent=l};document.addEventListener("click",(t=>{(e=>{const t=e.target.closest(`.${c}`);if(!t)return void a();if(null!==e.target.closest(`.${l}`))return;const o=t.querySelector(".wp-block-hiring-hub-query-loop-filtering-filter__dropdown");if(t.classList.contains(n)){const e=o.classList.contains(l);a(),e||(e=>{e.classList.add(l)})(o)}else a()})(t),(t=>{const o=t.target.closest(`.${c}`);if(!o)return;const{type:r}=o.dataset,l=o.querySelector('input[type="hidden"]');if(!l)return;if(t.target.closest(".wp-block-hiring-hub-query-loop-filtering-filter__clear-button"))return l.value="",b(o,r),void h(o);const n=t.target.closest(`.${i}`),s=t.target.closest(`.${i} button`);if(n&&s){const{value:t}=s.dataset;let i=e(l.value,[]);const c=i.indexOf(t);"checkbox"===r?-1!==c?(delete i[c],i=i.filter(Boolean),d(n,r)):(i.push(t),f(n,r)):"radio"===r&&(-1!==c?(i=[],d(n,r)):(i=[t],b(o,r),f(n,r))),l.value=(e=>JSON.stringify(e))(i),h(o,i)}})(t)})),document.addEventListener("keydown",(e=>{"Escape"!==e.key&&"Esc"!==e.key||a()}))};window.requestIdleCallback instanceof Function?window.requestIdleCallback(t):setTimeout(t,0)})();