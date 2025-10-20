import React, { useContext } from "react";
import { BrowserRouter as Router, Routes, Route, Navigate } from "react-router-dom";
import { AuthProvider, AuthContext } from "./context/AuthContext";
import Navbar from "./components/Navbar";
import Login from "./pages/Login";
import Dashboard from "./pages/Dashboard";

function AppContent() {
    const { user } = useContext(AuthContext);

    return (
        <Router>
            <Navbar />
            <main className="main">
                <Routes>
                    <Route
                        path="/"
                        element={user ? <Navigate to="/dashboard" /> : <Login />}
                    />
                    <Route path="/login" element={<Login />} />
                    <Route
                        path="/dashboard"
                        element={user ? <Dashboard /> : <Navigate to="/login" />}
                    />
                </Routes>
            </main>
        </Router>
    );
}

export default function App() {
    return (
        <AuthProvider>
            <AppContent />
        </AuthProvider>
    );
}
