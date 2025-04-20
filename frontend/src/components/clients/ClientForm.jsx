import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { getClient, createClient, updateClient } from '../../services/clientService';

const ClientForm = () => {
  const { id } = useParams();
  const navigate = useNavigate();
  const isEditMode = !!id;

  const [formData, setFormData] = useState({
    client_name: '',
    client_email: '',
    is_client_inv_req_auto_incre: 0,
    order_prefix: ''
  });
  
  const [loading, setLoading] = useState(isEditMode);
  const [error, setError] = useState(null);

  useEffect(() => {
    if (isEditMode) {
      fetchClientData();
    }
  }, [id]);

  const fetchClientData = async () => {
    try {
      const data = await getClient(id);
      setFormData({
        id: data.id,
        client_name: data.client_name,
        client_email: data.client_email,
        is_client_inv_req_auto_incre: data.is_client_inv_req_auto_incre,
        order_prefix: data.order_prefix
      });
      setLoading(false);
    } catch (err) {
      setError('Failed to fetch client details');
      setLoading(false);
      console.error(err);
    }
  };

  const handleChange = (e) => {
    const { name, value, type, checked } = e.target;
    setFormData(prevState => ({
      ...prevState,
      [name]: type === 'checkbox' ? (checked ? 1 : 0) : value
    }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    
    try {
      if (isEditMode) {
        await updateClient(formData);
      } else {
        await createClient(formData);
      }
      
      navigate('/clients');
    } catch (err) {
      setError(`Failed to ${isEditMode ? 'update' : 'create'} client`);
      console.error(err);
    }
  };

  if (loading) return <div>Loading...</div>;
  if (error) return <div className="error">{error}</div>;

  return (
    <div className="client-form">
      <h2>{isEditMode ? 'Edit Client' : 'Add New Client'}</h2>
      
      <form onSubmit={handleSubmit}>
        <div className="form-group">
          <label htmlFor="client_name">Client Name</label>
          <input
            type="text"
            className="form-control"
            id="client_name"
            name="client_name"
            value={formData.client_name}
            onChange={handleChange}
            required
          />
        </div>
        
        <div className="form-group">
          <label htmlFor="client_email">Email Address</label>
          <input
            type="email"
            className="form-control"
            id="client_email"
            name="client_email"
            value={formData.client_email}
            onChange={handleChange}
            required
          />
        </div>
        
        <div className="form-group">
          <label htmlFor="order_prefix">Order Prefix</label>
          <input
            type="text"
            className="form-control"
            id="order_prefix"
            name="order_prefix"
            value={formData.order_prefix}
            onChange={handleChange}
            required
            maxLength="10"
            placeholder="e.g. XET"
          />
          <small className="form-text text-muted">
            This prefix will be used for generating order numbers (e.g. XET-0045)
          </small>
        </div>
        
        <div className="form-group form-check">
          <input
            type="checkbox"
            className="form-check-input"
            id="is_client_inv_req_auto_incre"
            name="is_client_inv_req_auto_incre"
            checked={formData.is_client_inv_req_auto_incre === 1}
            onChange={handleChange}
          />
          <label className="form-check-label" htmlFor="is_client_inv_req_auto_incre">
            Auto-increment Invoice Numbers
          </label>
          <small className="form-text text-muted">
            If checked, invoice numbers for this client will be automatically generated
          </small>
        </div>
        
        <button type="submit" className="btn btn-primary">
          {isEditMode ? 'Update Client' : 'Create Client'}
        </button>
        <button 
          type="button" 
          className="btn btn-secondary ml-2"
          onClick={() => navigate('/clients')}
        >
          Cancel
        </button>
      </form>
    </div>
  );
};

export default ClientForm;
