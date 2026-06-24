import { Navigate, Route, Routes } from 'react-router-dom';
import LandingPage from '@/pages/LandingPage';

export function AppRoutes() {
  return (
    <Routes>
      <Route path="/" element={<LandingPage />} />
      <Route path="*" element={<Navigate to="/" replace />} />
    </Routes>
  );
}
