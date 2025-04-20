import React, { useState, useEffect } from 'react';
import { useParams, useNavigate, Link } from 'react-router-dom';
import { getInvoice } from '../../services/invoiceService';

const InvoiceDetails = () => {
  const { id } = useParams();
  const navigate = useNavigate();
  
  const [invoice, setInvoice] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    fetchInvoice();
  }, [id]);

  const fetchInvoice = async () => {
    try {
      setLoading(true);
      const data = await getInvoice(id);
      setInvoice(data);
      setLoading(false);
    } catch (err) {
      setError('Failed to fetch invoice details');
      setLoading(false);
      console.error(err);
    }
  };

  const calculateTotal = () => {
    if (!invoice || !invoice.items) return 0;
    return invoice.items.reduce((total, item) => total + parseFloat(item.price), 0);
  };

  const printInvoice = () => {
    window.print();
  };

  if (loading) return <div>Loading...</div>;
  if (error) return <div className="error">{error}</div>;
  if (!invoice) return <div>Invoice not found</div>;

  return (
    <div className="invoice-details">
      <div className="d-flex justify-content-between align-items-center mb-4">
        <h2>Invoice Details</h2>
        <div>
          <Link to={`/invoices/edit/${invoice.id}`} className="btn btn-warning mr-2">
            Edit
          </Link>
          <button onClick={printInvoice} className="btn btn-primary">
            Print
          </button>
        </div>
      </div>
      
      <div className="card print-section">
        <div className="card-body">
          <div className="row mb-4">
            <div className="col-md-6">
              <h4>Invoice: {invoice.order_no}</h4>
              <div>Date: {new Date(invoice.order_date).toLocaleDateString()}</div>
              <div>Payment Method: {invoice.payment_method}</div>
            </div>
            <div className="col-md-6 text-right">
              <h4>Client Information</h4>
              <div>{invoice.client_name}</div>
              <div>{invoice.client_email}</div>
            </div>
          </div>
          
          <div className="table-responsive">
            <table className="table table-bordered">
              <thead>
                <tr>
                  <th>Description</th>
                  <th width="15%" className="text-right">Amount</th>
                </tr>
              </thead>
              <tbody>
                {invoice.items.map((item, index) => (
                  <tr key={index}>
                    <td className={item.is_subtask ? 'pl-4' : ''}>
                      {item.is_subtask ? '↳ ' : ''}{item.task_desc}
                    </td>
                    <td className="text-right">${parseFloat(item.price).toFixed(2)}</td>
                  </tr>
                ))}
              </tbody>
              <tfoot>
                <tr>
                  <th className="text-right">Total:</th>
                  <th className="text-right">${calculateTotal().toFixed(2)}</th>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
      </div>
      
      <div className="mt-3">
        <button 
          onClick={() => navigate('/invoices')} 
          className="btn btn-secondary"
        >
          Back to Invoices
        </button>
      </div>
    </div>
  );
};

export default InvoiceDetails;
