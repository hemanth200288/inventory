import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { getInvoice, createInvoice, updateInvoice } from '../../services/invoiceService';
import { getClients } from '../../services/clientService';

const InvoiceForm = () => {
  const { id, clientId } = useParams();
  const navigate = useNavigate();
  const isEditMode = !!id;

  const [clients, setClients] = useState([]);
  const [formData, setFormData] = useState({
    client_id: clientId || '',
    order_no: '',
    order_date: new Date().toISOString().split('T')[0],
    payment_method: '',
    items: [{ task_desc: '', price: 0, is_subtask: 0 }]
  });
  
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    const fetchData = async () => {
      try {
        // Fetch clients for dropdown
        const clientsData = await getClients();
        setClients(clientsData.records || []);
        
        // If in edit mode, fetch invoice data
        if (isEditMode) {
          const invoiceData = await getInvoice(id);
          setFormData({
            id: invoiceData.id,
            client_id: invoiceData.client_id,
            order_no: invoiceData.order_no,
            order_date: invoiceData.order_date,
            payment_method: invoiceData.payment_method,
            items: invoiceData.items.length > 0 ? invoiceData.items : [{ task_desc: '', price: 0, is_subtask: 0 }]
          });
        }
        
        setLoading(false);
      } catch (err) {
        setError('Failed to fetch data');
        setLoading(false);
        console.error(err);
      }
    };

    fetchData();
  }, [id, isEditMode]);

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData(prevState => ({
      ...prevState,
      [name]: value
    }));
  };

  const handleItemChange = (index, e) => {
    const { name, value, type, checked } = e.target;
    const newItems = [...formData.items];
    
    if (type === 'checkbox') {
      newItems[index][name] = checked ? 1 : 0;
    } else if (name === 'price') {
      newItems[index][name] = parseFloat(value) || 0;
    } else {
      newItems[index][name] = value;
    }
    
    setFormData(prevState => ({
      ...prevState,
      items: newItems
    }));
  };

  const handleAddItem = () => {
    setFormData(prevState => ({
      ...prevState,
      items: [...prevState.items, { task_desc: '', price: 0, is_subtask: 0 }]
    }));
  };

  const handleRemoveItem = (index) => {
    if (formData.items.length <= 1) return; // Ensure at least one item
    
    const newItems = [...formData.items];
    newItems.splice(index, 1);
    
    setFormData(prevState => ({
      ...prevState,
      items: newItems
    }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    
    // Validate form data
    if (!formData.client_id) {
      setError('Please select a client');
      return;
    }
    
    if (formData.items.some(item => !item.task_desc)) {
      setError('All task descriptions are required');
      return;
    }
    
    try {
      if (isEditMode) {
        await updateInvoice(formData);
      } else {
        await createInvoice(formData);
      }
      
      navigate('/invoices');
    } catch (err) {
      setError(`Failed to ${isEditMode ? 'update' : 'create'} invoice`);
      console.error(err);
    }
  };

  if (loading) return <div>Loading...</div>;
  if (error) return <div className="error">{error}</div>;

  return (
    <div className="invoice-form">
      <h2>{isEditMode ? 'Edit Invoice' : 'Create New Invoice'}</h2>
      
      <form onSubmit={handleSubmit}>
        <div className="form-row">
          <div className="form-group col-md-6">
            <label htmlFor="client_id">Client</label>
            <select
              className="form-control"
              id="client_id"
              name="client_id"
              value={formData.client_id}
              onChange={handleChange}
              required
              disabled={isEditMode}
            >
              <option value="">--- Select Client ---</option>
              {clients.map(client => (
                <option key={client.id} value={client.id}>
                  {client.client_name}
                </option>
              ))}
            </select>
          </div>
          
          {isEditMode && (
            <div className="form-group col-md-6">
              <label htmlFor="order_no">Order Number</label>
              <input
                type="text"
                className="form-control"
                id="order_no"
                name="order_no"
                value={formData.order_no}
                onChange={handleChange}
                required
                disabled
              />
            </div>
          )}
        </div>
        
        <div className="form-row">
          <div className="form-group col-md-6">
            <label htmlFor="order_date">Order Date</label>
            <input
              type="date"
              className="form-control"
              id="order_date"
              name="order_date"
              value={formData.order_date}
              onChange={handleChange}
              required
            />
          </div>
          
          <div className="form-group col-md-6">
            <label htmlFor="payment_method">Payment Method</label>
            <input
              type="text"
              className="form-control"
              id="payment_method"
              name="payment_method"
              value={formData.payment_method}
              onChange={handleChange}
              placeholder="e.g. Credit Card, Bank Transfer"
              required
            />
          </div>
        </div>
        
        <h4>Invoice Items</h4>
        
        {formData.items.map((item, index) => (
          <div key={index} className="card mb-3">
            <div className="card-body">
              <div className="form-row">
                <div className="form-group col-md-8">
                  <label htmlFor={`task_desc_${index}`}>Task Description</label>
                  <input
                    type="text"
                    className="form-control"
                    id={`task_desc_${index}`}
                    name="task_desc"
                    value={item.task_desc}
                    onChange={(e) => handleItemChange(index, e)}
                    required
                  />
                </div>
                
                <div className="form-group col-md-3">
                  <label htmlFor={`price_${index}`}>Price</label>
                  <div className="input-group">
                    <div className="input-group-prepend">
                      <span className="input-group-text">$</span>
                    </div>
                    <input
                      type="number"
                      step="0.01"
                      min="0"
                      className="form-control"
                      id={`price_${index}`}
                      name="price"
                      value={item.price}
                      onChange={(e) => handleItemChange(index, e)}
                      required
                    />
                  </div>
                </div>
                
                <div className="form-group col-md-1 d-flex align-items-end">
                  <button
                    type="button"
                    className="btn btn-danger"
                    onClick={() => handleRemoveItem(index)}
                    disabled={formData.items.length <= 1}
                  >
                    X
                  </button>
                </div>
              </div>
              
              <div className="form-group form-check">
                <input
                  type="checkbox"
                  className="form-check-input"
                  id={`is_subtask_${index}`}
                  name="is_subtask"
                  checked={item.is_subtask === 1}
                  onChange={(e) => handleItemChange(index, e)}
                />
                <label className="form-check-label" htmlFor={`is_subtask_${index}`}>
                  This is a subtask
                </label>
              </div>
            </div>
          </div>
        ))}
        
        <button
          type="button"
          className="btn btn-secondary mb-3"
          onClick={handleAddItem}
        >
          Add More Items
        </button>
        
        <div className="form-group">
          <button type="submit" className="btn btn-primary">
            {isEditMode ? 'Update Invoice' : 'Create Invoice'}
          </button>
          <button 
            type="button" 
            className="btn btn-secondary ml-2"
            onClick={() => navigate('/invoices')}
          >
            Cancel
          </button>
        </div>
      </form>
    </div>
  );
};

export default InvoiceForm;
