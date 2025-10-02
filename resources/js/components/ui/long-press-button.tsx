import React, { useState, useRef, useEffect } from 'react';
import { Button } from './button';
import { cn } from '@/lib/utils';

interface LongPressButtonProps extends React.ButtonHTMLAttributes<HTMLButtonElement> {
  onLongPress: () => void;
  duration?: number;
  variant?: 'default' | 'destructive' | 'destructiveGhost' | 'outline' | 'secondary' | 'ghost' | 'link';
  children: React.ReactNode;
}

export function LongPressButton({
  onLongPress,
  duration = 1000,
  variant = 'destructiveGhost',
  children,
  className,
  ...props
}: LongPressButtonProps) {
  const [isPressed, setIsPressed] = useState(false);
  const [progress, setProgress] = useState(0);
  const timerRef = useRef<NodeJS.Timeout | null>(null);
  const progressRef = useRef<NodeJS.Timeout | null>(null);
  const startTimeRef = useRef<number>(0);

  const startPress = () => {
    setIsPressed(true);
    startTimeRef.current = Date.now();
    
    // Start progress animation
    progressRef.current = setInterval(() => {
      const elapsed = Date.now() - startTimeRef.current;
      const newProgress = Math.min((elapsed / duration) * 100, 100);
      setProgress(newProgress);
      
      if (newProgress >= 100) {
        clearInterval(progressRef.current!);
      }
    }, 16); // ~60fps

    // Set timer to trigger action
    timerRef.current = setTimeout(() => {
      onLongPress();
      resetPress();
    }, duration);
  };

  const cancelPress = () => {
    resetPress();
  };

  const resetPress = () => {
    if (timerRef.current) {
      clearTimeout(timerRef.current);
      timerRef.current = null;
    }
    if (progressRef.current) {
      clearInterval(progressRef.current);
      progressRef.current = null;
    }
    setIsPressed(false);
    setProgress(0);
  };

  useEffect(() => {
    return () => {
      if (timerRef.current) clearTimeout(timerRef.current);
      if (progressRef.current) clearInterval(progressRef.current);
    };
  }, []);

  return (
    <Button
      variant={variant}
      className={cn('relative overflow-hidden', className)}
      onMouseDown={startPress}
      onMouseUp={cancelPress}
      onMouseLeave={cancelPress}
      onTouchStart={startPress}
      onTouchEnd={cancelPress}
      {...props}
    >
      {/* Progress fill */}
      <span
        className="absolute inset-0 bg-destructive/30 transition-transform origin-left"
        style={{
          transform: `scaleX(${progress / 100})`,
          transition: isPressed ? 'none' : 'transform 0.2s ease-out',
        }}
      />
      
      {/* Button content */}
      <span className="relative z-10">{children}</span>
    </Button>
  );
}
