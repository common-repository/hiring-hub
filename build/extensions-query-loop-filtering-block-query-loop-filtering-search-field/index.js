(()=>{"use strict";var e,r={474:()=>{const e=window.wp.blockEditor,r=window.wp.blocks,t=window.wp.components,o=window.wp.element,n=window.wp.i18n,i=window.ReactJSXRuntime;(0,r.registerBlockType)("hiring-hub/query-loop-filtering-search-field",{edit:({attributes:r,setAttributes:l})=>{const{placeholder:s}=r,a=(0,e.useBlockProps)();return(0,i.jsxs)(o.Fragment,{children:[(0,i.jsx)(e.InspectorControls,{children:(0,i.jsx)(t.PanelBody,{title:(0,n.__)("Settings","hiring-hub"),children:(0,i.jsx)(t.TextControl,{label:(0,n.__)("Field's placeholder","hiring-hub"),value:s,onChange:e=>{l({placeholder:e})}})})}),(0,i.jsx)("input",{...a,type:"text",placeholder:s})]})},save:({attributes:r})=>{const{placeholder:t}=r,o=e.useBlockProps.save();return(0,i.jsx)("input",{...o,type:"text",placeholder:t,name:"hiring-hub-qlff[s]"})}})}},t={};function o(e){var n=t[e];if(void 0!==n)return n.exports;var i=t[e]={exports:{}};return r[e](i,i.exports,o),i.exports}o.m=r,e=[],o.O=(r,t,n,i)=>{if(!t){var l=1/0;for(c=0;c<e.length;c++){t=e[c][0],n=e[c][1],i=e[c][2];for(var s=!0,a=0;a<t.length;a++)(!1&i||l>=i)&&Object.keys(o.O).every((e=>o.O[e](t[a])))?t.splice(a--,1):(s=!1,i<l&&(l=i));if(s){e.splice(c--,1);var p=n();void 0!==p&&(r=p)}}return r}i=i||0;for(var c=e.length;c>0&&e[c-1][2]>i;c--)e[c]=e[c-1];e[c]=[t,n,i]},o.o=(e,r)=>Object.prototype.hasOwnProperty.call(e,r),(()=>{var e={57:0,350:0};o.O.j=r=>0===e[r];var r=(r,t)=>{var n,i,l=t[0],s=t[1],a=t[2],p=0;if(l.some((r=>0!==e[r]))){for(n in s)o.o(s,n)&&(o.m[n]=s[n]);if(a)var c=a(o)}for(r&&r(t);p<l.length;p++)i=l[p],o.o(e,i)&&e[i]&&e[i][0](),e[i]=0;return o.O(c)},t=self.webpackChunk=self.webpackChunk||[];t.forEach(r.bind(null,0)),t.push=r.bind(null,t.push.bind(t))})();var n=o.O(void 0,[350],(()=>o(474)));n=o.O(n)})();