'use client';

import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import { CheckIcon, CopyIcon } from 'lucide-react';
import type { ComponentProps, HTMLAttributes, ReactNode } from 'react';
import { createContext, useContext, useEffect, useState } from 'react';

type CodeBlockContextType = {
  code: string;
  language: string;
};

const CodeBlockContext = createContext<CodeBlockContextType>({
  code: '',
  language: 'typescript',
});

export type CodeBlockProps = HTMLAttributes<HTMLDivElement> & {
  code: string;
  language: string;
  children?: ReactNode;
};

export const CodeBlock = ({
  code,
  language,
  className,
  children,
  ...props
}: CodeBlockProps) => {
  const [highlightedCode, setHighlightedCode] = useState<string>('');
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    const loadHighlightedCode = async () => {
      try {
        const { codeToHtml } = await import('shiki');
        
        const html = await codeToHtml(code, {
          lang: language,
          themes: {
            light: 'vitesse-light',
            dark: 'vitesse-dark',
          },
        });

        setHighlightedCode(html);
        setIsLoading(false);
      } catch (error) {
        console.error(`Failed to highlight code for language "${language}":`, error);
        setIsLoading(false);
      }
    };

    loadHighlightedCode();
  }, [code, language]);

  return (
    <CodeBlockContext.Provider value={{ code, language }}>
      <div
        className={cn(
          'relative overflow-hidden rounded-md border bg-background',
          className
        )}
        {...props}
      >
        <div className="absolute top-2 right-2 z-10">
          {children}
        </div>
        {isLoading ? (
          <pre className="overflow-x-auto p-4">
            <code>{code}</code>
          </pre>
        ) : (
          <div
            className={cn(
              'overflow-x-auto',
              '[&_pre]:py-4',
              '[&_pre]:px-4',
              '[&_code]:text-sm',
              '[&_.shiki]:!bg-[var(--shiki-light-bg)]',
              'dark:[&_.shiki]:!bg-[var(--shiki-dark-bg)]',
            )}
            dangerouslySetInnerHTML={{ __html: highlightedCode }}
          />
        )}
      </div>
    </CodeBlockContext.Provider>
  );
};

export type CodeBlockCopyButtonProps = ComponentProps<typeof Button> & {
  onCopy?: () => void;
  onError?: (error: Error) => void;
  timeout?: number;
};

export const CodeBlockCopyButton = ({
  onCopy,
  onError,
  timeout = 2000,
  className,
  ...props
}: CodeBlockCopyButtonProps) => {
  const [isCopied, setIsCopied] = useState(false);
  const { code } = useContext(CodeBlockContext);

  const copyToClipboard = () => {
    if (
      typeof window === 'undefined' ||
      !navigator.clipboard.writeText ||
      !code
    ) {
      return;
    }

    navigator.clipboard.writeText(code).then(() => {
      setIsCopied(true);
      onCopy?.();

      setTimeout(() => setIsCopied(false), timeout);
    }, onError);
  };

  const Icon = isCopied ? CheckIcon : CopyIcon;

  return (
    <Button
      className={cn('shrink-0 h-8 w-8', className)}
      onClick={copyToClipboard}
      size="icon"
      variant="ghost"
      {...props}
    >
      <Icon className="text-muted-foreground" size={14} />
    </Button>
  );
};

