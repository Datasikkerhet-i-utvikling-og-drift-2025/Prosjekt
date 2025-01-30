package no.hiof.diuod.gruppe1.mobilapp.ui.screens

import android.content.Context
import androidx.compose.foundation.background
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.width
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.text.KeyboardOptions
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Email
import androidx.compose.material.icons.filled.Lock
import androidx.compose.material3.Button
import androidx.compose.material3.ExperimentalMaterial3Api
import androidx.compose.material3.Icon
import androidx.compose.material3.IconButton
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.OutlinedTextField
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.res.painterResource
import androidx.compose.ui.text.input.KeyboardType
import androidx.compose.ui.text.input.PasswordVisualTransformation
import androidx.compose.ui.text.input.VisualTransformation
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.unit.dp
import androidx.navigation.NavController
import no.hiof.diuod.gruppe1.mobilapp.ui.viewModels.LoginViewModel
import no.hiof.diuod.gruppe1.mobilapp.R


@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun LoginScreen(navController: NavController, viewModel: LoginViewModel = LoginViewModel(navController)){

    val uiState by viewModel.uiState
    val context = LocalContext.current
    var errorMessage by remember { mutableStateOf("") }

    Column(
        modifier = Modifier.fillMaxSize()
            .background(Color(0xFFF5F5F5)),
        verticalArrangement = Arrangement.Center,
        horizontalAlignment = Alignment.CenterHorizontally
    ) {

        Text(
            text = "Sign in",
            style = MaterialTheme.typography.headlineLarge,
            textAlign = TextAlign.Center,
            modifier = Modifier
                .align(Alignment.CenterHorizontally)
                .padding(top = 200.dp)
        )

        if (uiState.errorMessage.isNotEmpty()) {
            Text(
                text = uiState.errorMessage,
                color = Color.Red,
                textAlign = TextAlign.Center,
                modifier = Modifier
                    .align(Alignment.CenterHorizontally)
                    .padding(top = 10.dp)
                    .fillMaxWidth(0.9f)
            )
        }

        Column(
            modifier = Modifier.fillMaxSize(),
            verticalArrangement = Arrangement.Bottom,
            horizontalAlignment = Alignment.CenterHorizontally
        ) {

            Spacer(modifier = Modifier.weight(2f))

            EmailField(uiState.email, viewModel::onEmailChange)
            Spacer(modifier = Modifier.height(20.dp))
            PasswordField(uiState.password, viewModel::onPasswordChange)

            Spacer(modifier = Modifier.height(20.dp))

            Row {
                LoginButton(navController, viewModel, context)
                Spacer(modifier = Modifier.width(20.dp))
                RegisterButton(navController)
            }

            Spacer(modifier = Modifier.height(20.dp))

            ForgotPasswordButton(viewModel)
            Spacer(modifier = Modifier.weight(0.3f))
        }
    }
}

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun EmailField(
    value: String,
    onNewValue: (String) -> Unit,
    modifier: Modifier = Modifier.fillMaxWidth(0.9f)
) {
    OutlinedTextField(
        singleLine = true,
        modifier = modifier,
        value = value,
        onValueChange = { onNewValue(it) },
        placeholder = { Text("Email") },
        leadingIcon = { Icon(imageVector = Icons.Default.Email, contentDescription = "Email") }
    )
}

@Composable
fun PasswordField(
    value: String,
    onNewValue: (String) -> Unit,
    modifier: Modifier = Modifier.fillMaxWidth(0.9f)
) {
    var isVisible by remember { mutableStateOf(true) }
    var isVisibleToggled by remember { mutableStateOf(false) }

    val icon = if (isVisible) painterResource(R.drawable.ic_visibility_on) else painterResource(R.drawable.ic_visibility_off)
    val visualTransformation = if (isVisible) VisualTransformation.None else PasswordVisualTransformation()

    OutlinedTextField(
        singleLine = true,
        modifier = modifier,
        value = value,
        onValueChange ={
            onNewValue(it)
            if (!isVisibleToggled) isVisible = it == ""
        }               ,
        placeholder = { Text("Password") },
        leadingIcon = { Icon(imageVector = Icons.Default.Lock, contentDescription = "Password") },
        trailingIcon = {
            IconButton(onClick = {
                isVisible = !isVisible
                if (!isVisibleToggled) isVisibleToggled = true
            }) {
                Icon(painter = icon, contentDescription = if (isVisible) "Hide password" else "Show password")
            }
        },
        keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Password),
        visualTransformation = visualTransformation
    )
}


@Composable
private fun RegisterButton(navController: NavController) {
    Button(
        onClick = { /*navController.navigate(AppScreens.LOGIN_SELECTION.name)*/ },
        modifier = Modifier.width(150.dp),
        shape = RoundedCornerShape(10.dp)
    ) {
        Text("Register")
    }
}

@Composable
private fun LoginButton(navController: NavController, viewModel: LoginViewModel, context: Context) {
    Button(
        onClick = { viewModel.onLoginClick(context, navController) },
        modifier = Modifier.width(150.dp),
        shape = RoundedCornerShape(10.dp)
    ) {
        Text("Sign in")
    }
}

@Composable
private fun ForgotPasswordButton(viewModel: LoginViewModel) {
    Button(
        onClick = { viewModel.onForgotPasswordClick() },
        modifier = Modifier,
        shape = RoundedCornerShape(10.dp)
    ) {
        Text("Forgot password")
    }
}