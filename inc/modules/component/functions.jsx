export const renderComponents=()=>{
	const list=document.querySelectorAll('[data-component]');
	Array.prototype.forEach.call(list,(el)=>{
		ReactDOM.render(React.createElement(window[el.dataset.component]),{...el.dataset});
	});
};