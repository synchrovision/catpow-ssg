export const Slider=(props)=>{
	const {useState,useCallback,useEffect,useRef,useReducer}=React;
	const {className="cp-slider",children,loop=false,dots=true,arrow=true,onSwipeLeft,onSwipeRight}=props;
	const [isHold,setIsHold]=useState(false);
	const ref=useRef();
	const tmp=useRef({org:{x:0,w:0,c:0,t:0},crr:{x:0,t:0},diff:{x:0,p:0,t:0},delta:{x:0,t:0}});
	
	const reducer=useCallback((state,action)=>{
		switch(action.type){
			case 'PREV':
			case 'NEXT':
			case 'GOTO':{
				if(action.type==='PREV'){state.current--;}
				else if(action.type==='NEXT'){state.current++;}
				else{state.current=action.index;}
				if(loop){
					while(state.current<0){state.current+=children.length;}
					state.current%=children;
				}
				else{
					state.current=Math.max(0,Math.min(state.current,children.length-1));
				}
				return {...state};
			}
		}
		return state;
	},[children]);
	const [state,dispatch]=useReducer(reducer,{
		current:props.defaultSlide || 0
	});
	
	const getSlideClasses=useCallback((pos)=>{
		if(loop){}
		const classes=['slide','-slide'+pos];
		if(pos===0){classes.push('-current');}
		else if(pos<0){
			classes.push('-before');
			if(pos===-1){classes.push('-prev');}
		}
		else if(pos>0){
			classes.push('-after');
			if(pos===1){classes.push('-next');}
		}
		return classes.join(' ');
	},[loop]);
	const Control=useCallback((props)=>{
		const {current}=props;
		const isFirst=!loop && current < 1;
		const isLast=!loop && current >= children.length-1;
		return (
			<div className="controls">
				{arrow && (
					<div className="arrows">
						<div className={"arrow -left"+(isFirst?' -disabled':'')} onClick={()=>!isFirst && dispatch({type:'PREV'})}></div>
						<div className={"arrow -right"+(isLast?' -disabled':'')} onClick={()=>!isLast && dispatch({type:'NEXT'})}></div>
					</div>
				)}
				{dots && (
					<div className="dots">
						{[...Array(children.length).keys()].map((index)=>(
							<div className={"dot"+(index===current?' -active':'')} onClick={()=>dispatch({type:'GOTO',index})}></div>
						))}
					</div>
				)}
			</div>
		);
	},[arrow,dots,loop]);
	useEffect(()=>{
		const {org,crr,diff,delta}=tmp.current;
		const updateValue=(obj,e)=>{
			obj.x=e.targetTouches[0].clientX;
			obj.t=Date.now();
		};
		const handleTouchStart=(e)=>{
			updateValue(org,e);
			org.w=ref.current.offsetWidth;
			org.c=1/org.w;
			delta.x=delta.t=diff.p=diff.t=0;
			ref.current.style.setProperty('--slide-tx',0);
			ref.current.style.setProperty('--slide-p',0);
			ref.current.style.setProperty('--slide-abs-p',0);
			ref.current.style.setProperty('--slide-pl',0);
			ref.current.style.setProperty('--slide-pr',0);
		};
		const handleTouchMove=(e)=>{
			delta.x=e.targetTouches[0].clientX-crr.x;
			delta.t=Date.now()-crr.t;
			updateValue(crr,e);
			diff.x=crr.x-org.x;
			if(diff.x>=org.w){
				dispatch({type:'PREV'});
				diff.x-=org.w;
				org.x+=org.w;
			}
			else if(diff.x<=-org.w){
				dispatch({type:'NEXT'});
				diff.x+=org.w;
				org.x-=org.w;
			}
			diff.p=diff.x*org.c;
			diff.t=crr.t-org.t;
			ref.current.style.setProperty('--slide-tx',diff.x);
			ref.current.style.setProperty('--slide-p',diff.p);
			ref.current.style.setProperty('--slide-abs-p',Math.abs(diff.p));
			if(diff.p>0){
				ref.current.style.setProperty('--slide-pl',diff.p);
				ref.current.style.setProperty('--slide-pr',0);
			}
			else{
				ref.current.style.setProperty('--slide-pl',0);
				ref.current.style.setProperty('--slide-pr',Math.abs(diff.p));
			}
			e.preventDefault();
			setIsHold(true);
		};
		const handleTouchEnd=(e)=>{
			ref.current.style.setProperty('--slide-tx',0);
			ref.current.style.setProperty('--slide-p',0);
			ref.current.style.setProperty('--slide-abs-p',0);
			ref.current.style.setProperty('--slide-pl',0);
			ref.current.style.setProperty('--slide-pr',0);
			const d=delta.x/delta.t;
			if(diff.p>0.5 || d>1){dispatch({type:'PREV'});}
			else if(diff.p<-0.5 || d<-1){dispatch({type:'NEXT'});}
			setIsHold(false);
		};
		ref.current.addEventListener('touchstart',handleTouchStart);
		ref.current.addEventListener('touchmove',handleTouchMove);
		ref.current.addEventListener('touchend',handleTouchEnd);
		return ()=>{
			if(!ref.current){return;}
			ref.current.removeEventListener('touchstart',handleTouchStart);
			ref.current.removeEventListener('touchmove',handleTouchMove);
			ref.current.removeEventListener('touchend',handleTouchEnd);
		};
	},[ref.current,tmp.current,setIsHold]);
	
	return (
		<div className={className + (isHold?' -hold':'')} ref={ref}>
			<div class="slides">
				{children.map((child,index)=><div className={getSlideClasses(index-state.current)} key={index}>{child}</div>)}
			</div>
			<Control current={state.current}/>
		</div>
	);
}