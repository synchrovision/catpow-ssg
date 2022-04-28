﻿export const Transition=(props)=>{
	const {useState,useCallback,useEffect,useRef}=React;
	const {className='cp-transition',children,fitHeight=false}=props;
	const [contents,setContents]=useState(children);
	const [ready,setReady]=useState(false);
	
	const refContainer=useRef();
	const refPrev=useRef();
	const refCurrent=useRef();
	
	const [type,setType]=useState(props.type || 'init');
	
	
	const getTransitionType=useCallback((prev,next)=>{
		if(!prev.props || !next.props){return 'none';}
		const {depth=1,page=1,view='normal'}=next.props;
		const {depth:prevDeps=1,page:prevPage=1,view:prevView='normal'}=prev.props;
		if(depth!==prevDeps){
			return (depth>prevDeps)?'focus':'blur';
		}
		else if(page!==prevPage){
			return (page>prevPage)?'next':'prev';
		}
		else{
			return (view!==prevView)?'mod':'none';
		}
		return 'none';
	},[]);
	
	useEffect(()=>{
		const transitionType=getTransitionType(contents,children);
		if(transitionType==='none'){
			setContents(children);
			if(fitHeight){
				setTimeout(()=>{
					refContainer.current.style.height=refCurrent.current.clientHeight+'px';
				},1);
			}
		}
		else{
			setType(transitionType);
			setReady(true);
			setTimeout(()=>{
				setContents(children);
				setTimeout(()=>{
					if(fitHeight){
						refContainer.current.style.height=refCurrent.current.clientHeight+'px';
					}
					setReady(false);
				},1);
			},1);
		}
		return ()=>{
			if(!refPrev.current || !refCurrent.current){return;}
			refPrev.current.innerHTML=refCurrent.current.innerHTML;
		};
	},[children]);
	
	return (
		<div className={className+' -'+type} ref={refContainer}>
			<div className={"contents -prev -"+(ready?"from":"to")} ref={refPrev}></div>
			<div className={"contents -current -"+(ready?"from":"to")} ref={refCurrent}>{contents}</div>
		</div>
	);
}