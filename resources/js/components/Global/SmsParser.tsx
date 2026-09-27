import { useEffect, useState } from 'react'
import { getSms, updateSms, deleteSms, createSms } from '../../Api';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { LongPressButton } from '@/components/ui/long-press-button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { cn } from '@/lib/utils';

interface SmsParserProps {
  open: boolean;
  onOpenChange: (open: boolean) => void;
}

interface Sms {
  id: number;
  body: string;
  transaction_id: number | null;
}

export default function SmsParser({ open, onOpenChange }: SmsParserProps) {
  const [invalidSms, setInvalidSms] = useState<Sms[]>([]);
  const [loading, setLoading] = useState(false);
  const [editingSms, setEditingSms] = useState<Sms | null>(null);
  const [editBody, setEditBody] = useState('');
  const [newSmsBody, setNewSmsBody] = useState('');
  const [createdAt, setCreatedAt] = useState('');

  useEffect(() => {
    if (open) {
      fetchInvalidSms();
    } else {
      setEditingSms(null);
      setEditBody('');
    }
  }, [open]);

  const fetchInvalidSms = () => {
    setLoading(true);
    getSms(1)
      .then(({ data }) => {
        // Filter to show only invalid SMS (those without transaction_id)
        const invalid = data.sms.data.filter((sms: Sms) => !sms.transaction_id);
        setInvalidSms(invalid);
        setLoading(false);
      })
      .catch((error) => {
        console.error(error);
        setLoading(false);
      });
  };

  const handleEdit = (sms: Sms) => {
    setEditingSms(sms);
    setEditBody(sms.body);
  };

  const cancelEdit = () => {
    setEditingSms(null);
    setEditBody('');
  };

  const handleUpdate = () => {
    if (!editingSms || loading) return;

    setLoading(true);
    updateSms({ id: editingSms.id, body: editBody })
      .then(() => {
        fetchInvalidSms();
        cancelEdit();
      })
      .catch((error) => {
        console.error(error);
        setLoading(false);
      });
  };

  const handleDelete = (sms: Sms) => {
    deleteSms(sms.id)
      .then(() => {
        setInvalidSms(invalidSms.filter((item) => item.id !== sms.id));
        cancelEdit();
      })
      .catch(console.error);
  };

  const handleCreate = () => {
    if (!newSmsBody.trim() || loading) return;

    setLoading(true);
    createSms({ sms: newSmsBody, createdAt })
      .then(() => {
        fetchInvalidSms();
        setNewSmsBody('');
        setCreatedAt('');
      })
      .catch((error) => {
        console.error(error);
        setLoading(false);
      });
  };

  const hasUnparsed = invalidSms.length > 0;

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogContent
        aria-describedby={undefined}
        className={cn('flex flex-col gap-0 overflow-hidden p-0', hasUnparsed ? 'h-[85vh] max-h-[720px] sm:max-w-5xl' : 'sm:max-w-lg')}
      >
        <DialogHeader className="border-b p-4">
          <DialogTitle>SMS Parser</DialogTitle>
        </DialogHeader>

        <div className={cn('grid min-h-0 flex-1', hasUnparsed && 'grid-rows-2 md:grid-cols-2 md:grid-rows-1')}>
          {hasUnparsed && (
            <div className="flex min-h-0 flex-col border-b md:border-r md:border-b-0">
              <div className="flex items-center gap-2 px-4 pt-4 pb-2">
                <h3 className="text-sm font-medium">Unparsed</h3>
                <Badge variant="outline" className="border-red-200 bg-red-50 text-xs text-red-700">
                  {invalidSms.length}
                </Badge>
              </div>

              <div className="min-h-0 flex-1 space-y-2 overflow-y-auto px-4 pb-4">
                {invalidSms.map((sms) =>
                  editingSms?.id === sms.id ? (
                    <div key={sms.id} className="space-y-3 rounded-lg border border-primary/40 p-3">
                      <Textarea
                        name="body"
                        value={editBody}
                        autoFocus
                        className="min-h-24 w-full bg-white text-xs"
                        onChange={(e) => setEditBody(e.target.value)}
                      />
                      <div className="flex items-center justify-end gap-2">
                        <LongPressButton onLongPress={() => handleDelete(sms)} variant="destructiveGhost">
                          Hold to Delete
                        </LongPressButton>
                        <Button variant="outline" size="sm" onClick={cancelEdit}>
                          Cancel
                        </Button>
                        <Button size="sm" onClick={handleUpdate} disabled={loading || !editBody.trim()}>
                          {loading ? 'Parsing...' : 'Parse'}
                        </Button>
                      </div>
                    </div>
                  ) : (
                    <button
                      key={sms.id}
                      type="button"
                      onClick={() => handleEdit(sms)}
                      className={cn(
                        'w-full rounded-lg border p-3 text-left text-xs transition-colors hover:bg-accent',
                        editingSms && 'opacity-60',
                      )}
                    >
                      <span className="line-clamp-3">{sms.body}</span>
                    </button>
                  ),
                )}
              </div>
            </div>
          )}

          <div className="flex min-h-0 flex-col gap-3 overflow-y-auto p-4">
            <Textarea
              name="newSms"
              value={newSmsBody}
              onChange={(e) => setNewSmsBody(e.target.value)}
              className="min-h-40 w-full flex-1 bg-white"
              placeholder="Paste SMS messages, one per line"
            />

            <div className="flex items-center gap-3">
              <Input
                type="date"
                name="date"
                aria-label="Date"
                title="Leave empty for today"
                value={createdAt}
                onChange={(e) => setCreatedAt(e.target.value)}
                className="flex-1 bg-white"
              />
              <Button onClick={handleCreate} disabled={loading || !newSmsBody.trim()}>
                {loading ? 'Parsing...' : 'Parse'}
              </Button>
            </div>
          </div>
        </div>
      </DialogContent>
    </Dialog>
  );
}
