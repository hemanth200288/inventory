import React, { useState, useEffect } from 'react';
import { Link, useParams } from 'react-router-dom';
import { getInvoices, getInvoicesByClient, deleteInvoice } from '../../services/invoiceService';
import { getClient } from '../../services/clientService';

const InvoiceGrid = () => {
  const { clientId } = useParams();
  const [invoices, setInvoices] = useState([]);
  const [clientInfo, setClientInfo] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    fetchInvoices();
  }, [clientId]);

  const fetchInvoices = async () => {
    try {
      setLoading(true);
      let data;
      
      if (clientId) {
        // Get client info
        const client = await getClient(clientId);
        setClientInfo(client);
        
        // Get invoices for this client
        data = await getInvoicesByClient(clientId);
      } else {
        // Get all invoices
        data = await getInvoices();
      }
      
      setInvoices(data.records || []);
      setLoading(false);
    } catch (err) {
      setError('Failed to fetch invoices');
      setLoading(false);
      console.error(err);
    }
  };

  const handleDelete = async (id) => {
    if (window.confirm('Are you sure you want to delete this invoice?')) {
      try {
        await deleteInvoice(id);
        // Refresh the list
        fetchInvoices();
      } catch (err) {
        setError('Failed to delete invoice');
        console.error(err);
      }
    }
  };

  if (loading) return <div>Loading...</div>;
  if (error) return <div className="error">{error}</div>;

  return (
    <div className="invoice-grid">
      <div className="grid-header">
        <h2>
          {clientInfo 
            ? `Invoices for ${clientInfo.client_name}` 
            : 'All Invoices'}
        </h2>
        <div>
          {clientId && (
            <Link to="/invoices" className="btn btn-secondary mr-2">
              View All Invoices
            </Link>
          )}
          <Link 
            to={clientId ? `/invoices/new/${clientId}` : '/invoices/new'} 
            className="btn btn-primary"
          >
            Create New Invoice
          </Link>
        </div>
      </div>
      
      <table className="table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Order No</th>
            <th>Client</th>
            <th>Date</th>
            <th>Total</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          {invoices.length > 0 ? (
            invoices.map(invoice => (
              <tr key={invoice.id}>
                <td>{invoice.id}</td>
                <td>{invoice.order_no}</td>
                <td>{invoice.client_name}</td>
                <td>{new Date(invoice.order_date).toLocaleDateString()}</td>
                <td>${parseFloat(invoice.total).toFixed(2)}</td>
                <td>
                  <Link to={`/invoices/view/${invoice.id}`} className="btn btn-sm btn-info">
                    View
                  </Link>
                  <Link to={`/invoices/edit/${invoice.id}`} className="btn btn-sm btn-warning ml-2">
                    Edit
                  </Link>
                  <button 
                    onClick={() => handleDelete(invoice.id)} 
                    className="btn btn-sm btn-danger ml-2"
                  >
                    Delete
                  </button>
                </td>
              </tr>
            ))
          ) : (
            <tr>
              <td colSpan="6">No invoices found</td>
            </tr>
          )}
        </tbody>
      </table>
    </div>
  );
};

export default InvoiceGrid;
