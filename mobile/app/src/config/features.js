import BuyerDashboardScreen from '../screens/BuyerDashboardScreen';
import CartScreen from '../screens/CartScreen';
import CreateListingScreen from '../screens/CreateListingScreen';
import DashboardAnalysisScreen from '../screens/DashboardAnalysisScreen';
import DashboardScreen from '../screens/DashboardScreen';
import ForgotPasswordScreen from '../screens/ForgotPasswordScreen';
import HomeScreen from '../screens/HomeScreen';
import ListingsScreen from '../screens/ListingsScreen';
import LoginScreen from '../screens/LoginScreen';
import MaterialDetailScreen from '../screens/MaterialDetailScreen';
import MaterialsScreen from '../screens/MaterialsScreen';
import MessagesScreen from '../screens/MessagesScreen';
import ProfileScreen from '../screens/ProfileScreen';
import RegisterScreen from '../screens/RegisterScreen';
import SavedProductsScreen from '../screens/SavedProductsScreen';
import SettingsScreen from '../screens/SettingsScreen';
import SubscriptionScreen from '../screens/SubscriptionScreen';
import SupportScreen from '../screens/SupportScreen';
import SupplierKycScreen from '../screens/SupplierKycScreen';
import TermsScreen from '../screens/TermsScreen';

export const authScreens = [
  { name: 'Login', component: LoginScreen },
  { name: 'Register', component: RegisterScreen },
  { name: 'ForgotPassword', component: ForgotPasswordScreen },
];

export const publicScreens = [
  { name: 'Home', component: HomeScreen },
  { name: 'Materials', component: MaterialsScreen },
  { name: 'MaterialDetail', component: MaterialDetailScreen },
  { name: 'Terms', component: TermsScreen },
  { name: 'Support', component: SupportScreen },
];

export const protectedScreens = [
  { name: 'Dashboard', component: DashboardScreen },
  { name: 'DashboardAnalysis', component: DashboardAnalysisScreen },
  { name: 'BuyerDashboard', component: BuyerDashboardScreen },
  { name: 'Listings', component: ListingsScreen },
  { name: 'CreateListing', component: CreateListingScreen },
  { name: 'Cart', component: CartScreen },
  { name: 'Messages', component: MessagesScreen },
  { name: 'Profile', component: ProfileScreen },
  { name: 'Settings', component: SettingsScreen },
  { name: 'SavedProducts', component: SavedProductsScreen },
  { name: 'Subscription', component: SubscriptionScreen },
  { name: 'SupplierKyc', component: SupplierKycScreen },
];
