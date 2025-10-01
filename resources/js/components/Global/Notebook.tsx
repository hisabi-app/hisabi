import { useEffect, useState, useRef } from 'react';
import { XIcon } from '@heroicons/react/solid';
import { Plate, usePlateEditor } from 'platejs/react';
import type { Value } from 'platejs';
import { customQuery } from '../../Api';
import { BasicNodesKit } from '@/components/editor/plugins/basic-nodes-kit';
import { Editor, EditorContainer } from '@/components/ui/editor';

interface NotebookProps {
  onClose: () => void;
}

const initialValue: Value = [
  {
    type: 'p',
    children: [{ text: 'Start writing your notes here...' }],
  },
];

export default function Notebook({ onClose }: NotebookProps) {
  const [loading, setLoading] = useState(false);
  const [saving, setSaving] = useState(false);
  const [lastSaved, setLastSaved] = useState<Date | null>(null);
  const [initialContent, setInitialContent] = useState<Value>(initialValue);
  const [editorValue, setEditorValue] = useState<Value>(initialValue);
  const saveTimeoutRef = useRef<NodeJS.Timeout | null>(null);

  const editor = usePlateEditor({
    plugins: BasicNodesKit,
    value: initialContent,
  });

  // Load notebook content on mount
  useEffect(() => {
    loadNotebook();
  }, []);

  // Auto-save functionality
  useEffect(() => {
    if (loading) return;
    if (!editorValue || editorValue === initialContent) return;

    // Clear existing timeout
    if (saveTimeoutRef.current) {
      clearTimeout(saveTimeoutRef.current);
    }

    // Set new timeout for auto-save
    const timeoutId = setTimeout(() => {
      saveNotebook(editorValue);
    }, 2000); // Auto-save after 2 seconds of inactivity

    saveTimeoutRef.current = timeoutId;

    return () => {
      if (saveTimeoutRef.current) {
        clearTimeout(saveTimeoutRef.current);
      }
    };
  }, [editorValue, loading]);

  const loadNotebook = async () => {
    setLoading(true);
    try {
      const { data } = await customQuery('notebook { content }');
      if (data?.notebook?.content) {
        try {
          const parsedContent = JSON.parse(data.notebook.content);
          setInitialContent(parsedContent);
          setEditorValue(parsedContent);
          // Use the editor API to set value after a brief delay
          setTimeout(() => {
            editor.tf.setValue(parsedContent);
          }, 100);
        } catch (e) {
          // If content is not valid JSON, use initial value
          console.error('Failed to parse notebook content:', e);
        }
      }
    } catch (error) {
      console.error('Failed to load notebook:', error);
    } finally {
      setLoading(false);
    }
  };

  const saveNotebook = async (value: Value) => {
    setSaving(true);
    try {
      const contentString = JSON.stringify(value).replace(/"/g, '\\"');
      const mutation = `
        mutation {
          saveNotebook(content: "${contentString}") {
            content
            updated_at
          }
        }
      `;
      await customQuery(mutation);
      setLastSaved(new Date());
    } catch (error) {
      console.error('Failed to save notebook:', error);
    } finally {
      setSaving(false);
    }
  };

  return (
    <div className="h-full w-full flex flex-col overflow-hidden">
      {/* Header */}
      <div className="p-4 border-r border-b">
        <div className='flex justify-between items-center'>
          <h2 className='text-lg font-semibold'>My Notebook</h2>
          <div className="flex items-center gap-2">
            {saving && (
              <span className="text-xs text-muted-foreground">Saving...</span>
            )}
            {lastSaved && !saving && (
              <span className="text-xs text-muted-foreground">
                Saved {lastSaved.toLocaleTimeString()}
              </span>
            )}
            <button
              onClick={onClose}
              className="text-muted-foreground hover:text-foreground"
            >
              <XIcon className='w-5 h-5' />
            </button>
          </div>
        </div>
      </div>

      {/* Editor Content */}
      <div className="flex-1 overflow-hidden border-r">
        {loading ? (
          <div className="flex items-center justify-center h-full">
            <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
          </div>
        ) : (
          <Plate 
            editor={editor}
            onChange={({ value }) => {
              setEditorValue(value);
            }}
          >
            <EditorContainer variant="default">
              <Editor 
                variant="default"
                placeholder="Start writing your notes here..."
              />
            </EditorContainer>
          </Plate>
        )}
      </div>
    </div>
  );
}

