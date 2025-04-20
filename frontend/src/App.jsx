import React from 'react';
import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';

// Import layout
import Layout from './components/common/Layout';

// Import pages
import ClientsPage from './pages/ClientsPage';
import CreateClientPage from './pages/CreateClientPage';
import InvoicesPage from './pages/InvoicesPage';
import CreateInvoicePage from './pages/CreateInvoicePage';
import ViewInvoicePage from './pages/ViewInvoicePage';

function App() {
  return (
    <Router>
      <Layout>
        <Routes>
          {/* Default redirect to clients page */}
          <Route path="/" element={<Navigate to="/clients" replace />} />
          
          {/* Client routes */}
          <Route path="/clients" element={<ClientsPage />} />
          <Route path="/clients/new" element={<CreateClientPage />} />
          <Route path="/clients/edit/:id" element={<CreateClientPage />} />
          
          {/* Invoice routes */}
          <Route path="/invoices" element={<InvoicesPage />} />
          <Route path="/invoices/client/:clientId" element={<InvoicesPage />} />
          <Route path="/invoices/new" element={<CreateInvoicePage />} />
          <Route path="/invoices/new/:clientId" element={<CreateInvoicePage />} />
          <Route path="/invoices/edit/:id" element={<CreateInvoicePage />} />
          <Route path="/invoices/view/:id" element={<ViewInvoicePage />} />
          
          {/* Catch-all route */}
          <Route path="*" element={<Navigate to="/clients" replace />} />
        </Routes>
      </Layout>
    </Router>
  );
}

export default App;
