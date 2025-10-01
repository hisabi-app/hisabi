import { useEffect, useState } from 'react'
import { XIcon } from '@heroicons/react/solid';
import { getSms, updateSms, deleteResource, createSms } from '../../Api';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';
import { LongPressButton } from '@/components/ui/long-press-button';
import { AtSymbolIcon } from '@heroicons/react/solid';

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
  const [showCreateForm, setShowCreateForm] = useState(false);
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
    setShowCreateForm(false);
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
        setShowCreateForm(false);
        setLoading(false);
      })
      .catch((error) => {
        console.error(error);
        setLoading(false);
      });
  };

  return (
    <div className="h-full w-full flex flex-col overflow-hidden">
      <div className="border-r p-4">
        <div className='flex justify-between items-center mb-2'>
          <h2 className='text-lg font-semibold'>SMS Parser</h2>
          <button
            onClick={onClose}
            className="text-muted-foreground hover:text-foreground"
          >
            <XIcon className='w-5 h-5' />
          </button>
        </div>
        <p className='text-xs text-muted-foreground'>
          Review and fix invalid SMS messages that couldn't be parsed.
        </p>
      </div>

      <div className="flex-1 flex flex-col overflow-hidden border-r">
        {/* Edit Form or SMS List */}
        {editingSms ? (
          <div className="flex-1 flex flex-col p-4 overflow-y-auto">
            <div className="mb-4">
              <Button
                variant="ghost"
                size="sm"
                onClick={() => {
                  setEditingSms(null);
                  setEditBody('');
                }}
                className="mb-4"
              >
                ← Back to list
              </Button>

              <div className="p-3 rounded border-l-2 border-orange-500 pl-3 bg-orange-50 text-xs mb-4">
                To ensure correct parsing, add the corresponding SMS template in <span className="bg-orange-100 rounded px-1 font-mono">config/hisabi.php</span> under <span className="bg-orange-100 rounded px-1 font-mono">sms_templates</span>.
              </div>

              <div>
                <Label htmlFor="body" className="text-sm font-medium">
                  SMS Body
                </Label>
                <textarea
                  name="body"
                  value={editBody}
                  onChange={(e) => setEditBody(e.target.value)}
                  className="mt-2 w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                  rows={6}
                />
              </div>

              <div className="flex items-center justify-between mt-4">
                <LongPressButton
                  onLongPress={() => handleDelete(editingSms)}
                  variant="destructive"
                >
                  Hold to Delete
                </LongPressButton>
                <Button onClick={handleUpdate} disabled={loading}>
                  {loading ? 'Parsing...' : 'Parse again'}
                </Button>
              </div>
            </div>
          </div>
        ) : showCreateForm ? (
          <div className="flex-1 flex flex-col p-4 overflow-y-auto">
            <div className="mb-4">
              <Button
                variant="ghost"
                size="sm"
                onClick={() => {
                  setShowCreateForm(false);
                  setNewSmsBody('');
                  setCreatedAt('');
                }}
                className="mb-4"
              >
                ← Back to list
              </Button>

              <div>
                <Label htmlFor="newSms" className="text-sm font-medium">
                  SMS Message(s)
                </Label>
                <small className="text-muted-foreground block mb-2">You can parse multiple messages one per line</small>
                <textarea
                  name="newSms"
                  value={newSmsBody}
                  onChange={(e) => setNewSmsBody(e.target.value)}
                  className="mt-2 w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                  rows={8}
                  placeholder="Paste your SMS messages here..."
                />
              </div>

              <div className="mt-4">
                <Label htmlFor="date" className="text-sm font-medium">
                  Default Transaction Date (Optional)
                </Label>
                <Input
                  type="date"
                  name="date"
                  value={createdAt}
                  onChange={(e) => setCreatedAt(e.target.value)}
                  className="mt-2"
                />
              </div>

              <div className="flex items-center justify-end mt-4">
                <Button onClick={handleCreate} disabled={loading || !newSmsBody.trim()}>
                  {loading ? 'Parsing...' : 'Parse SMS'}
                </Button>
              </div>
            </div>
          </div>
        ) : (
          <div className="flex-1 flex flex-col p-4 overflow-y-auto">
            <div className="mb-4">
              <Button
                onClick={() => setShowCreateForm(true)}
                className="w-full"
                size="sm"
              >
                + Parse New SMS
              </Button>
            </div>

            {loading && invalidSms.length === 0 ? (
              <div className="flex items-center justify-center h-full">
                <AtSymbolIcon className="animate-spin text-primary h-6 w-6 mr-2" />
                <span className="text-sm text-muted-foreground">Loading...</span>
              </div>
            ) : invalidSms.length === 0 ? (
              <div className="flex flex-col items-center justify-center h-full text-center px-4">
                <div className="text-green-500 text-4xl mb-2">✓</div>
                <p className="text-sm font-medium">All SMS parsed successfully!</p>
                <p className="text-xs text-muted-foreground mt-1">
                  No invalid messages to review.
                </p>
              </div>
            ) : (
              <div className="space-y-2">
                <div className="mb-3">
                  <p className="text-xs text-muted-foreground">
                    {invalidSms.length} invalid message{invalidSms.length !== 1 ? 's' : ''} found
                  </p>
                </div>
                {invalidSms.map((sms) => (
                  <Card key={sms.id} className="py-0 cursor-pointer hover:bg-accent/50 transition-colors">
                    <CardContent
                      className="px-3 py-3"
                      onClick={() => handleEdit(sms)}
                    >
                      <div className="flex items-start gap-2">
                        <Badge variant="outline" className="bg-red-50 text-red-700 border-red-200 text-xs shrink-0">
                          Invalid
                        </Badge>
                        <p className="text-xs flex-1 line-clamp-2">
                          {sms.body}
                        </p>
                      </div>
                    </CardContent>
                  </Card>
                ))}
              </div>
            )}
          </div>
        )}
      </div>
    </div>
  );
}

