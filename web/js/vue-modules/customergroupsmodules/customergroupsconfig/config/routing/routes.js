import { generalconfig } from '../../components/generalconfig/';

const routes = [
    {
        path: '/',
        name: 'index',
        component: generalconfig,
        meta: { requiresAuth: false }
    }
];

export default routes;