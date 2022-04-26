export const TabPanel=(props)=>{
	const {useState,useCallback,useEffect,useRef,useReducer}=React;
	const {className="tabpanel",children}=props;
	const [current,setCurrent]=useState(props.initialOpen || 0);
	
	return (
		<div className={className}>
			<div className="tabs">
			{children.map((child,index)=>(
				<div className={"tab"+(current===index?' -active':'')} onClick={()=>setCurrent(index)} key={child.key}>{child.key}</div>
			))}
			</div>
			<div class="contents">{children[current]}</div>
		</div>
	);
}