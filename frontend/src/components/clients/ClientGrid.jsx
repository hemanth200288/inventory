import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { getClients, deleteClient } from '../../services/clientService';

const ClientGrid = () => {
  const [clients, setClients] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    fetchClients();
  }, []);

  const fetchClients = async () => {
    try {
      setLoading(true);
      const data = await getClients();
      setClients(data.records || []);
      setLoading(false);
    } catch (err) {
      setError('Failed to fetch clients');
      setLoading(false);
      console.error(err);
    }
  };

  const handleDelete = async (id) => {
    if (window.confirm('Are you sure you want to delete this client?')) {
      try {
        await deleteClient(id);
        // Refresh the list
        fetchClients();
      } catch (err) {
        setError('Failed to delete client');
        console.error(err);
      }
    }
  };

  if (loading) return <div>Loading...</div>;
  if (error) return <div className="error">{error}</div>;

  return (
    <div className="client-grid">
      <div className="grid-header">
        <h2>Clients</h2>
        <Link to="/clients/new" className="btn btn-primary">Add New Client</Link>
      </div>
      
      <table className="table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Client Name</th>
            <th>Email Address</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          {clients.length > 0 ? (
            clients.map(client => (
              <tr key={client.id}>
                <td>{client.id}</td>
                <td>{client.client_name}</td>
                <td>{client.client_email}</td>
                <td>
                  <Link to={`/clients/edit/${client.id}`} className="btn btn-sm btn-info">Edit</Link>
                  <button 
                    onClick={() => handleDelete(client.id)} 
                    className="btn btn-sm btn-danger ml-2"
                  >
                    Delete
                  </button>
                  <Link to={`/invoices/client/${client.id}`} className="btn btn-sm btn-secondary ml-2">Invoices</Link>
                </td>
              </tr>
            ))
          ) : (
            <tr>
              <td colSpan="4">No clients found</td>
            </tr>
          )}
        </tbody>
      </table>
    </div>
  );
};

export default ClientGrid;
