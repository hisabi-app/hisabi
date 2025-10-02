import { useEffect, useState } from 'react'
import { XIcon } from '@heroicons/react/solid';
import { getSms, updateSms, deleteResource, createSms } from '../../Api';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';
import { LongPressButton } from '@/components/ui/long-press-button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';

interface SmsParserProps {
  onClose: () => void;
}

interface Sms {
  id: number;
  body: string;
  transaction_id: number | null;
}

export default function SmsParser({ onClose }: SmsParserProps) {
  const [invalidSms, setInvalidSms] = useState<Sms[]>([]);
  const [loading, setLoading] = useState(false);
  const [editingSms, setEditingSms] = useState<Sms | null>(null);
  const [editBody, setEditBody] = useState('');
  const [newSmsBody, setNewSmsBody] = useState('');
  const [createdAt, setCreatedAt] = useState('');

  useEffect(() => {
    fetchInvalidSms();
  }, []);

  const fetchInvalidSms = () => {
    setLoading(true);
    getSms(1, '')
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

  const handleUpdate = () => {
    if (!editingSms || loading) return;

    setLoading(true);
    updateSms({ id: editingSms.id, body: editBody })
      .then(({ data }) => {
        // Refresh the list to see if it's now valid
        fetchInvalidSms();
        setEditingSms(null);
        setEditBody('');
        setLoading(false);
      })
      .catch((error) => {
        console.error(error);
        setLoading(false);
      });
  };

  const handleDelete = (sms: Sms) => {
    deleteResource({ id: sms.id, resource: 'Sms' })
      .then(() => {
        setInvalidSms(invalidSms.filter((item) => item.id !== sms.id));
        setEditingSms(null);
      })
      .catch(console.error);
  };

  const handleCreate = () => {
    if (!newSmsBody.trim() || loading) return;

    setLoading(true);
    createSms({ sms: newSmsBody, createdAt })
      .then(({ data }) => {
        // Refresh the list
        fetchInvalidSms();
        setNewSmsBody('');
        setCreatedAt('');
        setLoading(false);
      })
      .catch((error) => {
        console.error(error);
        setLoading(false);
      });
  };

  return (
    <div className="h-full w-full flex flex-col overflow-hidden">
      {/* Header */}
      <div className="border-b p-4">
        <div className='flex justify-between items-center'>
          <h2 className='text-lg font-semibold'>SMS Parser</h2>
          <button
            onClick={onClose}
            className="text-muted-foreground hover:text-foreground"
          >
            <XIcon className='w-5 h-5' />
          </button>
        </div>
      </div>

      {/* Invalid SMS Cards - Scrollable Top Section */}
      <div className="flex-1 overflow-y-auto p-4 border-r">
        {editingSms ? (
          <div className="mb-4">
            <Button
              variant="outline"
              size="sm"
              onClick={() => {
                setEditingSms(null);
                setEditBody('');
              }}
              className="mb-4"
            >Back</Button>
            <div>
              <Input
                name="body"
                value={editBody}
                className="w-full bg-white"
                onChange={(e) => setEditBody(e.target.value)}
              />
            </div>

            <div className="flex items-center justify-end gap-2 mt-4">
              <LongPressButton
                onLongPress={() => handleDelete(editingSms)}
                variant="destructiveGhost"
              >
                Hold to Delete
              </LongPressButton>
              <Button onClick={handleUpdate} disabled={loading}>
                {loading ? 'Parsing...' : 'Parse again'}
              </Button>
            </div>
          </div>
        ) : loading && invalidSms.length === 0 ? (
          <div className="flex items-center justify-center h-full">
            <span className="text-sm text-muted-foreground">Loading...</span>
          </div>
        ) : invalidSms.length > 0 && (
          <div className="space-y-2">
            <div className="mb-3">
              <p className="text-xs text-muted-foreground">
                {invalidSms.length} invalid message{invalidSms.length !== 1 ? 's' : ''}, edit and parse again
              </p>
            </div>
            {invalidSms.map((sms) => (
              <Card key={sms.id} className="py-0 cursor-pointer transition-colors">
                <CardContent
                  className="px-3 py-3"
                  onClick={() => handleEdit(sms)}
                >
                  <div className="flex items-center gap-2">
                    <p className="text-xs flex-1 line-clamp-2">
                      {sms.body}
                    </p>

                    <Badge variant="outline" className="bg-red-50 text-red-700 border-red-200 text-xs shrink-0">
                      Invalid
                    </Badge>
                  </div>
                </CardContent>
              </Card>
            ))}
          </div>
        )}
      </div>

      {/* Fixed Bottom Input Area - Chat-like */}
      <div className="border-t border-r p-4">
        <div className="space-y-3">
          <div>
            <Textarea
              name="newSms"
              value={newSmsBody}
              onChange={(e) => setNewSmsBody(e.target.value)}
              className="w-full bg-white min-h-36"
              placeholder="Paste SMS messages here (one per line)..."
            />
          </div>

          <div className='grid gap-1'>
            <Label htmlFor="date" className="text-xs text-muted-foreground">
              Transaction(s) date (leave blank for today)
            </Label>
            <Input
              type="date"
              name="date"
              value={createdAt}
              onChange={(e) => setCreatedAt(e.target.value)}
              className="w-full bg-white"
            />
          </div>

          {/* Parse Button */}
          <div className="flex justify-end">
            <Button
              onClick={handleCreate}
              disabled={loading || !newSmsBody.trim()}
              className="w-full sm:w-auto"
            >
              {loading ? 'Parsing...' : 'Parse SMS'}
            </Button>
          </div>
        </div>
      </div>
    </div>
  );
}

